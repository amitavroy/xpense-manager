<?php

use App\Actions\AddVehicleAction;
use App\Models\User;
use App\Models\Vehicle;

beforeEach(function () {
    $this->action = new AddVehicleAction;
    $this->user = User::factory()->create();
});

test('can execute add vehicle action with minimal data', function () {
    $data = [
        'name' => 'Honda City',
        'company_name' => 'Honda',
        'registration_number' => 'MH12AB1234',
    ];

    $vehicle = $this->action->execute($data, $this->user);

    expect($vehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Honda City')
        ->company_name->toBe('Honda')
        ->registration_number->toBe('MH12AB1234')
        ->kilometers->toBe(0)
        ->user_id->toBe($this->user->id);
});

test('can execute add vehicle action with all data provided', function () {
    $data = [
        'name' => 'Toyota Camry',
        'company_name' => 'Toyota',
        'registration_number' => 'DL8CD5678',
        'kilometers' => 50000,
    ];

    $vehicle = $this->action->execute($data, $this->user);

    expect($vehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Toyota Camry')
        ->company_name->toBe('Toyota')
        ->registration_number->toBe('DL8CD5678')
        ->kilometers->toBe(50000)
        ->user_id->toBe($this->user->id);
});

test('can execute add vehicle action with zero kilometers', function () {
    $data = [
        'name' => 'New Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
        'kilometers' => 0,
    ];

    $vehicle = $this->action->execute($data, $this->user);

    expect($vehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->kilometers->toBe(0)
        ->user_id->toBe($this->user->id);
});

test('can execute add vehicle action with kilometers defaulting to zero when not provided', function () {
    $data = [
        'name' => 'New Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
    ];

    $vehicle = $this->action->execute($data, $this->user);

    expect($vehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->kilometers->toBe(0);
});

test('can execute add vehicle action with special characters in name', function () {
    $data = [
        'name' => 'Vehicle & Co. (Special) - 2024',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
    ];

    $vehicle = $this->action->execute($data, $this->user);

    expect($vehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Vehicle & Co. (Special) - 2024');
});

test('can execute add vehicle action with different users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $data = [
        'name' => 'User Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
    ];

    $vehicle1 = $this->action->execute($data, $user1);
    $vehicle2 = $this->action->execute($data, $user2);

    expect($vehicle1)
        ->toBeInstanceOf(Vehicle::class)
        ->user_id->toBe($user1->id);

    expect($vehicle2)
        ->toBeInstanceOf(Vehicle::class)
        ->user_id->toBe($user2->id);

    expect($vehicle1->id)->not->toBe($vehicle2->id);
});

test('can execute add vehicle action multiple times for same user', function () {
    $data1 = [
        'name' => 'First Vehicle',
        'company_name' => 'Company 1',
        'registration_number' => 'REG001',
    ];

    $data2 = [
        'name' => 'Second Vehicle',
        'company_name' => 'Company 2',
        'registration_number' => 'REG002',
    ];

    $vehicle1 = $this->action->execute($data1, $this->user);
    $vehicle2 = $this->action->execute($data2, $this->user);

    expect($vehicle1)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('First Vehicle')
        ->user_id->toBe($this->user->id);

    expect($vehicle2)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Second Vehicle')
        ->user_id->toBe($this->user->id);

    expect($vehicle1->id)->not->toBe($vehicle2->id);
});

test('vehicle is persisted to database after execution', function () {
    $data = [
        'name' => 'Persistent Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'PERSIST123',
    ];

    $vehicle = $this->action->execute($data, $this->user);

    expect($vehicle->exists)->toBeTrue();
    expect($vehicle->id)->not->toBeNull();

    // Verify it can be retrieved from database
    $retrievedVehicle = Vehicle::find($vehicle->id);
    expect($retrievedVehicle)
        ->toBeInstanceOf(Vehicle::class)
        ->name->toBe('Persistent Vehicle')
        ->company_name->toBe('Test Company')
        ->registration_number->toBe('PERSIST123')
        ->kilometers->toBe(0)
        ->user_id->toBe($this->user->id);
});

test('action returns same vehicle instance that was created', function () {
    $data = [
        'name' => 'Return Test Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'RETURN123',
    ];

    $vehicle = $this->action->execute($data, $this->user);

    // Verify the returned vehicle is the same instance that was created
    expect($vehicle->exists)->toBeTrue();
    expect($vehicle->wasRecentlyCreated)->toBeTrue();
    // Check that the vehicle has the expected attributes
    expect($vehicle->name)->toBe('Return Test Vehicle');
    expect($vehicle->company_name)->toBe('Test Company');
    expect($vehicle->registration_number)->toBe('RETURN123');
});
