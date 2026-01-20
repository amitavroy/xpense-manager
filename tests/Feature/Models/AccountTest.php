<?php

use App\Enums\AccountTypeEnum;
use App\Models\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('account has fillable attributes', function () {
    $account = new Account;

    expect($account->getFillable())->toBe([
        'name',
        'type',
        'balance',
        'credit_limit',
        'currency',
        'is_active',
        'user_id',
    ]);
});

test('account has correct casts', function () {
    $account = new Account;

    expect($account->getCasts())->toMatchArray([
        'is_active' => 'boolean',
        'type' => AccountTypeEnum::class,
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ]);
});

test('account belongs to user', function () {
    $account = new Account;

    expect($account->user())
        ->toBeInstanceOf(BelongsTo::class);
});
