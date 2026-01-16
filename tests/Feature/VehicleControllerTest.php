<?php

use App\Models\User;
use App\Models\Vehicle;

test('guests cannot access vehicles index', function () {
    $this->get(route('vehicles.index'))->assertRedirect(route('login'));
});

test('authenticated users can view vehicles index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Vehicle::factory()->create(['user_id' => $user->id]);

    $this->get(route('vehicles.index'))->assertOk();
});

test('authenticated users can view only their own vehicles', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->actingAs($user);

    $userVehicle = Vehicle::factory()->create(['user_id' => $user->id]);
    Vehicle::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('vehicles.index'));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->has('vehicles.data', 1)
            ->where('vehicles.data.0.id', $userVehicle->id)
    );
});

test('authenticated users can view create vehicle form', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('vehicles.create'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('vehicle'));
});

test('authenticated users can store vehicle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $data = [
        'name' => 'Honda City',
        'company_name' => 'Honda',
        'registration_number' => 'MH12AB1234',
        'kilometers' => 50000,
    ];

    $response = $this->post(route('vehicles.store'), $data);
    $response->assertRedirect();

    $this->assertDatabaseHas('vehicles', [
        'user_id' => $user->id,
        'name' => 'Honda City',
        'company_name' => 'Honda',
        'registration_number' => 'MH12AB1234',
        'kilometers' => 50000,
    ]);
});

test('authenticated users can store vehicle with default kilometers', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $data = [
        'name' => 'New Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
    ];

    $response = $this->post(route('vehicles.store'), $data);
    $response->assertRedirect();

    $this->assertDatabaseHas('vehicles', [
        'user_id' => $user->id,
        'name' => 'New Vehicle',
        'kilometers' => 0,
    ]);
});

test('authenticated users can view edit form for their own vehicle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('vehicles.edit', $vehicle));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('vehicle.id', $vehicle->id)
    );
});

test('authenticated users cannot view edit form for other users vehicles', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('vehicles.edit', $vehicle));
    $response->assertForbidden();
});

test('authenticated users can view their own vehicle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('vehicles.show', $vehicle));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('vehicle.id', $vehicle->id)
    );
});

test('authenticated users cannot view other users vehicles', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('vehicles.show', $vehicle));
    $response->assertForbidden();
});

test('vehicle owner can update vehicle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $data = [
        'name' => 'Updated Vehicle',
        'company_name' => 'Updated Company',
        'registration_number' => 'UPDATED123',
        'kilometers' => 75000,
    ];

    $response = $this->put(route('vehicles.update', $vehicle), $data);
    $response->assertRedirect();

    $this->assertDatabaseHas('vehicles', [
        'id' => $vehicle->id,
        'name' => 'Updated Vehicle',
        'company_name' => 'Updated Company',
        'registration_number' => 'UPDATED123',
        'kilometers' => 75000,
    ]);
});

test('non-owner cannot update vehicle', function () {
    $user = User::factory()->create();
    $vehicleOwner = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $vehicleOwner->id]);

    $data = [
        'name' => 'Updated Vehicle',
        'company_name' => 'Updated Company',
        'registration_number' => 'UPDATED123',
        'kilometers' => 75000,
    ];

    $response = $this->put(route('vehicles.update', $vehicle), $data);
    $response->assertForbidden();
});

test('vehicle owner can archive vehicle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $response = $this->delete(route('vehicles.destroy', $vehicle));
    $response->assertRedirect(route('vehicles.index'));

    expect($vehicle->fresh()->trashed())->toBeTrue();
    expect(Vehicle::count())->toBe(0);
    expect(Vehicle::withTrashed()->count())->toBe(1);
});

test('non-owner cannot archive vehicle', function () {
    $user = User::factory()->create();
    $vehicleOwner = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $vehicleOwner->id]);

    $response = $this->delete(route('vehicles.destroy', $vehicle));
    $response->assertForbidden();

    expect($vehicle->fresh()->trashed())->toBeFalse();
});

test('archived vehicles are not shown in index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activeVehicle = Vehicle::factory()->create(['user_id' => $user->id]);
    $archivedVehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $archivedVehicle->delete();

    $response = $this->get(route('vehicles.index'));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->has('vehicles.data', 1)
            ->where('vehicles.data.0.id', $activeVehicle->id)
    );
});

test('store vehicle validates required fields', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('vehicles.store'), []);

    $response->assertSessionHasErrors(['name', 'company_name', 'registration_number']);
});

test('update vehicle validates required fields', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $response = $this->put(route('vehicles.update', $vehicle), []);

    $response->assertSessionHasErrors(['name', 'company_name', 'registration_number']);
});

test('kilometers must be integer and non-negative', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $data = [
        'name' => 'Test Vehicle',
        'company_name' => 'Test Company',
        'registration_number' => 'TEST123',
        'kilometers' => -100,
    ];

    $response = $this->post(route('vehicles.store'), $data);
    $response->assertSessionHasErrors(['kilometers']);
});
