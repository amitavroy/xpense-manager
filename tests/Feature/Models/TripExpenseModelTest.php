<?php

use App\Models\TripExpense;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

test('trip expense has fillable attributes', function () {
    $expense = new TripExpense;

    expect($expense->getFillable())
        ->toBe([
            'trip_id',
            'paid_by',
            'date',
            'amount',
            'description',
            'is_shared',
        ]);
});

test('trip expense has correct casts', function () {
    $expense = new TripExpense;

    expect($expense->getCasts())->toMatchArray([
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_shared' => 'boolean',
    ]);
});

test('trip expense belongs to trip', function () {
    $expense = new TripExpense;

    expect($expense->trip())
        ->toBeInstanceOf(BelongsTo::class);
});

test('trip expense belongs to paid by user', function () {
    $expense = new TripExpense;

    expect($expense->paidBy())
        ->toBeInstanceOf(BelongsTo::class);
});

test('trip expense belongs to many shared with users', function () {
    $expense = new TripExpense;

    expect($expense->sharedWith())
        ->toBeInstanceOf(BelongsToMany::class);
});
