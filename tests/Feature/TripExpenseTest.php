<?php

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\User;

test('guests cannot access trip expenses', function () {
    $trip = Trip::factory()->create();
    $this->get(route('trips.expenses.index', $trip))->assertRedirect(route('login'));
});

test('authenticated users can view trip expenses for trip they created', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);
    TripExpense::factory()->create(['trip_id' => $trip->id, 'paid_by' => $user->id]);

    $response = $this->get(route('trips.expenses.index', $trip));
    $response->assertOk();
});

test('authenticated users can view trip expenses for trip they are members of', function () {
    $user = User::factory()->create();
    $tripOwner = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $tripOwner->id]);
    $trip->members()->attach($user->id);

    $response = $this->get(route('trips.expenses.index', $trip));
    $response->assertOk();
});

test('authenticated users cannot view trip expenses for trip they are not members of', function () {
    $user = User::factory()->create();
    $tripOwner = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $tripOwner->id]);

    $response = $this->get(route('trips.expenses.index', $trip));
    $response->assertForbidden();
});

test('authenticated users can create trip expense', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);

    // Expenses are created via modal on trip show page, not via a separate create route
    // This test verifies the trip show page is accessible where the modal is used
    $response = $this->get(route('trips.show', $trip));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('trips/show')
    );
});

test('authenticated users can store trip expense', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);

    $data = [
        'trip_id' => $trip->id,
        'date' => '2024-06-05',
        'amount' => 100.50,
        'description' => 'Hotel booking',
        'is_shared' => false,
    ];

    $response = $this->post(route('trips.expenses.store', $trip), $data);
    $response->assertRedirect();

    $this->assertDatabaseHas('trip_expenses', [
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
        'date' => '2024-06-05 00:00:00',
        'amount' => 100.50,
        'description' => 'Hotel booking',
        'is_shared' => 0,
    ]);
});

test('authenticated users can store shared trip expense', function () {
    $user = User::factory()->create();
    $member = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);
    $trip->members()->attach($member->id);

    $data = [
        'trip_id' => $trip->id,
        'date' => '2024-06-05',
        'amount' => 200.00,
        'description' => 'Shared meal',
        'is_shared' => true,
        'shared_with' => [$member->id],
    ];

    $response = $this->post(route('trips.expenses.store', $trip), $data);
    $response->assertRedirect();

    $expense = TripExpense::where('description', 'Shared meal')->first();
    expect($expense->sharedWith)->toHaveCount(1);
    expect($expense->sharedWith->first()->id)->toBe($member->id);
});

test('authenticated users can update trip expense', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);
    $expense = TripExpense::factory()->create([
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
    ]);

    $data = [
        'trip_id' => $trip->id,
        'date' => '2024-06-10',
        'amount' => 150.00,
        'description' => 'Updated expense',
        'is_shared' => false,
    ];

    $response = $this->put(route('trips.expenses.update', [$trip, $expense]), $data);
    $response->assertRedirect();

    $this->assertDatabaseHas('trip_expenses', [
        'id' => $expense->id,
        'description' => 'Updated expense',
        'amount' => 150.00,
    ]);
});

test('authenticated users can delete trip expense', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $user->id]);
    $expense = TripExpense::factory()->create([
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
    ]);

    $response = $this->delete(route('trips.expenses.destroy', [$trip, $expense]));
    $response->assertRedirect();

    $this->assertDatabaseMissing('trip_expenses', ['id' => $expense->id]);
});

test('cannot create expense for trip user is not member of', function () {
    $user = User::factory()->create();
    $tripOwner = User::factory()->create();
    $this->actingAs($user);

    $trip = Trip::factory()->create(['user_id' => $tripOwner->id]);

    $data = [
        'trip_id' => $trip->id,
        'date' => '2024-06-05',
        'amount' => 100.50,
        'description' => 'Unauthorized expense',
        'is_shared' => false,
    ];

    $response = $this->post(route('trips.expenses.store', $trip), $data);
    $response->assertForbidden();
});
