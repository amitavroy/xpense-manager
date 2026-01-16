<?php

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('vehicle has fillable attributes', function () {
    $vehicle = new Vehicle;

    expect($vehicle->getFillable())
        ->toBe([
            'name',
            'company_name',
            'registration_number',
            'kilometers',
            'user_id',
        ]);
});

test('vehicle has correct casts', function () {
    $vehicle = new Vehicle;

    expect($vehicle->getCasts())->toMatchArray([
        'kilometers' => 'integer',
    ]);
});

test('vehicle has user relationship', function () {
    $vehicle = new Vehicle;

    expect($vehicle->user())
        ->toBeInstanceOf(BelongsTo::class);
});

test('vehicle belongs to user', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    expect($vehicle->user)
        ->toBeInstanceOf(User::class)
        ->id->toBe($user->id);
});

test('vehicle uses soft deletes', function () {
    $vehicle = Vehicle::factory()->create();

    expect($vehicle->deleted_at)->toBeNull();

    $vehicle->delete();

    expect($vehicle->deleted_at)->not->toBeNull();
    expect($vehicle->trashed())->toBeTrue();
});

test('vehicle kilometers defaults to zero', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::create([
        'user_id' => $user->id,
        'name' => 'Test Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
        'kilometers' => 0,
    ]);

    expect($vehicle->kilometers)->toBe(0);
});

test('vehicle kilometers is cast to integer', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::create([
        'user_id' => $user->id,
        'name' => 'Test Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
        'kilometers' => '50000',
    ]);

    expect($vehicle->kilometers)->toBeInt()->toBe(50000);
});

test('archived vehicle is not included in normal queries', function () {
    $user = User::factory()->create();
    $activeVehicle = Vehicle::factory()->create(['user_id' => $user->id]);
    $archivedVehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $archivedVehicle->delete();

    $vehicles = Vehicle::all();

    expect($vehicles)->toHaveCount(1)
        ->first()->id->toBe($activeVehicle->id);
});

test('archived vehicle can be retrieved with withTrashed', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $vehicle->delete();

    expect(Vehicle::withTrashed()->count())->toBe(1);
    expect(Vehicle::withTrashed()->first()->id)->toBe($vehicle->id);
});
