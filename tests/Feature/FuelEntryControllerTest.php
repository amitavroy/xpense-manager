<?php

use App\Models\User;
use App\Models\Vehicle;

test('authenticated users can see fuel entry form with preselected vehicle when vehicle_id query is provided', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('fuel-entry.create', ['vehicle_id' => $vehicle->id]));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('fuelEntry.vehicle_id', $vehicle->id)
            ->has('vehicles')
            ->has('accounts')
    );
});
