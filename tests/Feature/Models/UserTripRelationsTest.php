<?php

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('user has many trips', function () {
    $user = new User;

    expect($user->trips())
        ->toBeInstanceOf(HasMany::class);
});

test('user belongs to many member trips', function () {
    $user = new User;

    expect($user->memberTrips())
        ->toBeInstanceOf(BelongsToMany::class);
});

test('user has many trip expenses', function () {
    $user = new User;

    expect($user->tripExpenses())
        ->toBeInstanceOf(HasMany::class);
});

test('user belongs to many shared trip expenses', function () {
    $user = new User;

    expect($user->sharedTripExpenses())
        ->toBeInstanceOf(BelongsToMany::class);
});

test('user can create trip', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->create(['user_id' => $user->id]);

    expect($trip->user_id)->toBe($user->id);
    $user->load('trips');
    expect($user->trips->pluck('id'))->toContain($trip->id);
});

test('user can be added to trip as member', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->create();
    $trip->members()->attach($user->id);

    $trip->load('members');
    $user->load('memberTrips');
    expect($trip->members->pluck('id'))->toContain($user->id);
    expect($user->memberTrips->pluck('id'))->toContain($trip->id);
});

test('user can pay for trip expense', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->create();
    $expense = TripExpense::factory()->create([
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
    ]);

    expect($expense->paid_by)->toBe($user->id);
    $user->load('tripExpenses');
    expect($user->tripExpenses->pluck('id'))->toContain($expense->id);
});

test('user can share trip expense with others', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trip = Trip::factory()->create();
    $expense = TripExpense::factory()->create([
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
        'is_shared' => true,
    ]);
    $expense->sharedWith()->attach($otherUser->id);

    $expense->load('sharedWith');
    $otherUser->load('sharedTripExpenses');
    expect($expense->sharedWith->pluck('id'))->toContain($otherUser->id);
    expect($otherUser->sharedTripExpenses->pluck('id'))->toContain($expense->id);
});
