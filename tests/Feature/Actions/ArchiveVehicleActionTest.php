<?php

use App\Actions\ArchiveVehicleAction;
use App\Models\User;
use App\Models\Vehicle;

beforeEach(function () {
    $this->action = new ArchiveVehicleAction;
    $this->user = User::factory()->create();
});

test('can archive vehicle', function () {
    $vehicle = Vehicle::factory()->create(['user_id' => $this->user->id]);

    expect($vehicle->deleted_at)->toBeNull();
    expect($vehicle->trashed())->toBeFalse();

    $this->action->execute($vehicle);

    $vehicle->refresh();
    expect($vehicle->deleted_at)->not->toBeNull();
    expect($vehicle->trashed())->toBeTrue();
});

test('archived vehicle is soft deleted not hard deleted', function () {
    $vehicle = Vehicle::factory()->create(['user_id' => $this->user->id]);
    $vehicleId = $vehicle->id;

    $this->action->execute($vehicle);

    // Vehicle should still exist in database
    expect(Vehicle::withTrashed()->find($vehicleId))
        ->toBeInstanceOf(Vehicle::class)
        ->id->toBe($vehicleId);
});

test('archived vehicle is not included in normal queries', function () {
    $activeVehicle = Vehicle::factory()->create(['user_id' => $this->user->id]);
    $vehicleToArchive = Vehicle::factory()->create(['user_id' => $this->user->id]);

    $this->action->execute($vehicleToArchive);

    $vehicles = Vehicle::all();

    expect($vehicles)->toHaveCount(1)
        ->first()->id->toBe($activeVehicle->id);
});

test('archived vehicle can be retrieved with withTrashed', function () {
    $vehicle = Vehicle::factory()->create(['user_id' => $this->user->id]);

    $this->action->execute($vehicle);

    $archivedVehicles = Vehicle::withTrashed()->get();

    expect($archivedVehicles)->toHaveCount(1)
        ->first()->id->toBe($vehicle->id)
        ->first()->trashed()->toBeTrue();
});

test('can archive multiple vehicles', function () {
    $vehicle1 = Vehicle::factory()->create(['user_id' => $this->user->id]);
    $vehicle2 = Vehicle::factory()->create(['user_id' => $this->user->id]);
    $vehicle3 = Vehicle::factory()->create(['user_id' => $this->user->id]);

    $this->action->execute($vehicle1);
    $this->action->execute($vehicle2);

    expect(Vehicle::count())->toBe(1);
    expect(Vehicle::withTrashed()->count())->toBe(3);
    expect(Vehicle::find($vehicle3->id))->not->toBeNull();
});

test('archived vehicle retains all original attributes', function () {
    $vehicle = Vehicle::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
        'kilometers' => 50000,
    ]);

    $this->action->execute($vehicle);

    $archivedVehicle = Vehicle::withTrashed()->find($vehicle->id);

    expect($archivedVehicle)
        ->name->toBe('Test Vehicle')
        ->company_name->toBe('Test Company')
        ->registration_number->toBe('TEST123')
        ->kilometers->toBe(50000)
        ->user_id->toBe($this->user->id);
});
