<?php

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('trip has fillable attributes', function () {
    $trip = new Trip;

    expect($trip->getFillable())
        ->toBe([
            'user_id',
            'name',
            'start_date',
            'end_date',
        ]);
});

test('trip has correct casts', function () {
    $trip = new Trip;

    expect($trip->getCasts())->toMatchArray([
        'start_date' => 'date',
        'end_date' => 'date',
    ]);
});

test('trip belongs to user', function () {
    $trip = new Trip;

    expect($trip->user())
        ->toBeInstanceOf(BelongsTo::class);
});

test('trip belongs to many members', function () {
    $trip = new Trip;

    expect($trip->members())
        ->toBeInstanceOf(BelongsToMany::class);
});

test('trip has many expenses', function () {
    $trip = new Trip;

    expect($trip->expenses())
        ->toBeInstanceOf(HasMany::class);
});

test('trip can calculate total expenses by user', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->create(['user_id' => $user->id]);

    TripExpense::factory()->create([
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
        'amount' => 100.00,
    ]);

    TripExpense::factory()->create([
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
        'amount' => 50.00,
    ]);

    expect($trip->getTotalExpensesByUser($user->id))->toBe(150.00);
});

test('trip can calculate total shared expenses for user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trip = Trip::factory()->create(['user_id' => $user->id]);
    $trip->members()->attach($otherUser->id);

    $expense = TripExpense::factory()->create([
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
        'amount' => 100.00,
        'is_shared' => true,
    ]);
    $expense->sharedWith()->attach($otherUser->id);

    expect($trip->getTotalSharedExpensesForUser($otherUser->id))->toBe(100.00);
});

test('trip can calculate total non-shared expenses for user', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->create(['user_id' => $user->id]);

    TripExpense::factory()->create([
        'trip_id' => $trip->id,
        'paid_by' => $user->id,
        'amount' => 100.00,
        'is_shared' => false,
    ]);

    expect($trip->getTotalNonSharedExpensesForUser($user->id))->toBe(100.00);
});
