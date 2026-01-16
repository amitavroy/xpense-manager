<?php

use App\Actions\UpdateVehicleAction;
use App\Models\User;
use App\Models\Vehicle;

beforeEach(function () {
    $this->action = new UpdateVehicleAction;
    $this->user = User::factory()->create();
    $this->vehicle = Vehicle::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Original Name',
        'company_name' => 'Original Company',
        'registration_number' => 'ORIG123',
        'kilometers' => 10000,
    ]);
});

test('can update vehicle name', function () {
    $data = [
        'name' => 'Updated Name',
        'company_name' => $this->vehicle->company_name,
        'registration_number' => $this->vehicle->registration_number,
        'kilometers' => $this->vehicle->kilometers,
    ];

    $updatedVehicle = $this->action->execute($this->vehicle, $data);

    expect($updatedVehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Updated Name')
        ->company_name->toBe('Original Company')
        ->registration_number->toBe('ORIG123')
        ->kilometers->toBe(10000);
});

test('can update vehicle company name', function () {
    $data = [
        'name' => $this->vehicle->name,
        'company_name' => 'Updated Company',
        'registration_number' => $this->vehicle->registration_number,
        'kilometers' => $this->vehicle->kilometers,
    ];

    $updatedVehicle = $this->action->execute($this->vehicle, $data);

    expect($updatedVehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Original Name')
        ->company_name->toBe('Updated Company');
});

test('can update vehicle registration number', function () {
    $data = [
        'name' => $this->vehicle->name,
        'company_name' => $this->vehicle->company_name,
        'registration_number' => 'UPDATED123',
        'kilometers' => $this->vehicle->kilometers,
    ];

    $updatedVehicle = $this->action->execute($this->vehicle, $data);

    expect($updatedVehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->registration_number->toBe('UPDATED123');
});

test('can update vehicle kilometers', function () {
    $data = [
        'name' => $this->vehicle->name,
        'company_name' => $this->vehicle->company_name,
        'registration_number' => $this->vehicle->registration_number,
        'kilometers' => 50000,
    ];

    $updatedVehicle = $this->action->execute($this->vehicle, $data);

    expect($updatedVehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->kilometers->toBe(50000);
});

test('can update all vehicle attributes at once', function () {
    $data = [
        'name' => 'Fully Updated',
        'company_name' => 'New Company',
        'registration_number' => 'NEW123',
        'kilometers' => 75000,
    ];

    $updatedVehicle = $this->action->execute($this->vehicle, $data);

    expect($updatedVehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Fully Updated')
        ->company_name->toBe('New Company')
        ->registration_number->toBe('NEW123')
        ->kilometers->toBe(75000);
});

test('update persists to database', function () {
    $data = [
        'name' => 'Persisted Update',
        'company_name' => $this->vehicle->company_name,
        'registration_number' => $this->vehicle->registration_number,
        'kilometers' => $this->vehicle->kilometers,
    ];

    $this->action->execute($this->vehicle, $data);

    $retrievedVehicle = Vehicle::find($this->vehicle->id);
    expect($retrievedVehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Persisted Update');
});

test('update returns the same vehicle instance', function () {
    $data = [
        'name' => 'Return Test',
        'company_name' => $this->vehicle->company_name,
        'registration_number' => $this->vehicle->registration_number,
        'kilometers' => $this->vehicle->kilometers,
    ];

    $updatedVehicle = $this->action->execute($this->vehicle, $data);

    expect($updatedVehicle->id)->toBe($this->vehicle->id);

    // Verify it's the same vehicle by checking from database
    $retrievedVehicle = Vehicle::find($this->vehicle->id);
    expect($retrievedVehicle->id)->toBe($this->vehicle->id);
    expect($retrievedVehicle->name)->toBe('Return Test');
});

test('can update kilometers to zero', function () {
    $data = [
        'name' => $this->vehicle->name,
        'company_name' => $this->vehicle->company_name,
        'registration_number' => $this->vehicle->registration_number,
        'kilometers' => 0,
    ];

    $updatedVehicle = $this->action->execute($this->vehicle, $data);

    expect($updatedVehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->kilometers->toBe(0);
});

test('user_id is not changed during update', function () {
    $data = [
        'name' => 'Updated Name',
        'company_name' => $this->vehicle->company_name,
        'registration_number' => $this->vehicle->registration_number,
        'kilometers' => $this->vehicle->kilometers,
    ];

    $updatedVehicle = $this->action->execute($this->vehicle, $data);

    expect($updatedVehicle->user_id)->toBe($this->user->id);
});
