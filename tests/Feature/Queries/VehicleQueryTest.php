<?php

use App\Models\User;
use App\Models\Vehicle;
use App\Queries\VehicleQuery;
use Illuminate\Database\Eloquent\Builder;

test('forUser returns a builder instance', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $query = new VehicleQuery;

    $result = $query->forUser();

    expect($result)->toBeInstanceOf(Builder::class);
});

test('forUser returns only vehicles for authenticated user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Vehicle::factory()->create(['user_id' => $user1->id, 'name' => 'User1 Vehicle']);
    Vehicle::factory()->create(['user_id' => $user2->id, 'name' => 'User2 Vehicle']);

    $this->actingAs($user1);

    $query = new VehicleQuery;
    $vehicles = $query->forUser()->get();

    expect($vehicles)->toHaveCount(1);
    expect($vehicles->first()->user_id)->toBe($user1->id);
    expect($vehicles->first()->name)->toBe('User1 Vehicle');
});

test('forUser excludes archived vehicles', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activeVehicle = Vehicle::factory()->create(['user_id' => $user->id, 'name' => 'Active Vehicle']);
    $archivedVehicle = Vehicle::factory()->create(['user_id' => $user->id, 'name' => 'Archived Vehicle']);

    $archivedVehicle->delete();

    $query = new VehicleQuery;
    $vehicles = $query->forUser()->get();

    expect($vehicles)->toHaveCount(1);
    expect($vehicles->first()->id)->toBe($activeVehicle->id);
    expect($vehicles->first()->name)->toBe('Active Vehicle');
});

test('forUser orders vehicles by name', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Vehicle::factory()->create(['user_id' => $user->id, 'name' => 'Zebra Vehicle']);
    Vehicle::factory()->create(['user_id' => $user->id, 'name' => 'Alpha Vehicle']);
    Vehicle::factory()->create(['user_id' => $user->id, 'name' => 'Beta Vehicle']);

    $query = new VehicleQuery;
    $vehicles = $query->forUser()->get();

    expect($vehicles)->toHaveCount(3);
    expect($vehicles->first()->name)->toBe('Alpha Vehicle');
    expect($vehicles->get(1)->name)->toBe('Beta Vehicle');
    expect($vehicles->last()->name)->toBe('Zebra Vehicle');
});

test('forUser can be paginated', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Vehicle::factory()->count(15)->create(['user_id' => $user->id]);

    $query = new VehicleQuery;
    $paginated = $query->forUser()->paginate(10);

    expect($paginated->items())->toHaveCount(10);
    expect($paginated->total())->toBe(15);
    expect($paginated->hasMorePages())->toBeTrue();
});

test('forUser returns empty collection when user has no vehicles', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $query = new VehicleQuery;
    $vehicles = $query->forUser()->get();

    expect($vehicles)->toHaveCount(0);
});

test('forUser filters correctly for multiple users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    Vehicle::factory()->count(3)->create(['user_id' => $user1->id]);
    Vehicle::factory()->count(2)->create(['user_id' => $user2->id]);
    Vehicle::factory()->count(4)->create(['user_id' => $user3->id]);

    $this->actingAs($user2);

    $query = new VehicleQuery;
    $vehicles = $query->forUser()->get();

    expect($vehicles)->toHaveCount(2);
    expect($vehicles->every(fn ($vehicle) => $vehicle->user_id === $user2->id))->toBeTrue();
});
