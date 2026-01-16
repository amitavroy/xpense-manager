<?php

use App\Models\Trip;
use App\Models\User;

test('guests cannot access trips index', function () {
    $this->get(route('trips.index'))->assertRedirect(route('login'));
});

test('authenticated users can view trips index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Trip::factory()->create(['user_id' => $user->id]);

    $this->get(route('trips.index'))->assertOk();
});

test('authenticated users can view trips they created', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('trips.index'));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('trips/index')
            ->has('trips.data', 1)
            ->where('trips.data.0.id', $trip->id)
    );
});

test('authenticated users can view trips they are members of', function () {
    $user = User::factory()->create();
    $tripOwner = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $tripOwner->id]);
    $trip->members()->attach($user->id);

    $response = $this->get(route('trips.index'));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('trips/index')
            ->has('trips.data', 1)
            ->where('trips.data.0.id', $trip->id)
    );
});

test('authenticated users can create trip', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('trips.create'));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('trips/create')
    );
});

test('authenticated users can store trip', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $data = [
        'name' => 'Summer Vacation',
        'start_date' => '2024-06-01',
        'end_date' => '2024-06-15',
        'user_ids' => [],
    ];

    $response = $this->post(route('trips.store'), $data);
    $response->assertRedirect();

    $this->assertDatabaseHas('trips', [
        'user_id' => $user->id,
        'name' => 'Summer Vacation',
        'start_date' => '2024-06-01 00:00:00',
        'end_date' => '2024-06-15 00:00:00',
    ]);
});

test('authenticated users can store trip with members', function () {
    $user = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $this->actingAs($user);

    $data = [
        'name' => 'Summer Vacation',
        'start_date' => '2024-06-01',
        'end_date' => '2024-06-15',
        'user_ids' => [$member1->id, $member2->id],
    ];

    $response = $this->post(route('trips.store'), $data);
    $response->assertRedirect();

    $trip = Trip::where('name', 'Summer Vacation')->first();
    expect($trip->members)->toHaveCount(2);
});

test('authenticated users can view trip they created', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('trips.show', $trip));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('trips/show')
            ->where('trip.id', $trip->id)
    );
});

test('authenticated users can view trip they are members of', function () {
    $user = User::factory()->create();
    $tripOwner = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $tripOwner->id]);
    $trip->members()->attach($user->id);

    $response = $this->get(route('trips.show', $trip));
    $response->assertOk();
});

test('authenticated users cannot view trip they are not members of', function () {
    $user = User::factory()->create();
    $tripOwner = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $tripOwner->id]);

    $response = $this->get(route('trips.show', $trip));
    $response->assertForbidden();
});

test('trip owner can update trip', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);

    $data = [
        'name' => 'Updated Trip Name',
        'start_date' => '2024-07-01',
        'end_date' => '2024-07-15',
        'user_ids' => [],
    ];

    $response = $this->put(route('trips.update', $trip), $data);
    $response->assertRedirect();

    $this->assertDatabaseHas('trips', [
        'id' => $trip->id,
        'name' => 'Updated Trip Name',
    ]);
});

test('non-owner cannot update trip', function () {
    $user = User::factory()->create();
    $tripOwner = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $tripOwner->id]);

    $data = [
        'name' => 'Updated Trip Name',
        'start_date' => '2024-07-01',
        'end_date' => '2024-07-15',
        'user_ids' => [],
    ];

    $response = $this->put(route('trips.update', $trip), $data);
    $response->assertForbidden();
});

test('trip owner can delete trip', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);

    $response = $this->delete(route('trips.destroy', $trip));
    $response->assertRedirect(route('trips.index'));

    $this->assertDatabaseMissing('trips', ['id' => $trip->id]);
});

test('non-owner cannot delete trip', function () {
    $user = User::factory()->create();
    $tripOwner = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $tripOwner->id]);

    $response = $this->delete(route('trips.destroy', $trip));
    $response->assertForbidden();
});
