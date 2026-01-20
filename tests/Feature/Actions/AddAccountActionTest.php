<?php

use App\Actions\AddAccountAction;
use App\Enums\AccountTypeEnum;
use App\Models\Account;
use App\Models\User;

beforeEach(function () {
    $this->action = new AddAccountAction;
    $this->user = User::factory()->create();
});

test('can execute add account action with minimal data', function () {
    $data = [
        'name' => 'Test Account',
        'type' => AccountTypeEnum::BANK,
        'balance' => 1000.00,
    ];

    $account = $this->action->execute($data, $this->user);

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('Test Account')
        ->type->toBe(AccountTypeEnum::BANK)
        ->balance->toBe('1000.00')
        ->currency->toBe('INR')
        ->is_active->toBeTrue()
        ->user_id->toBe($this->user->id);
});

test('can execute add account action with all data provided', function () {
    $data = [
        'name' => 'Premium Savings Account',
        'type' => AccountTypeEnum::CREDIT_CARD,
        'balance' => 5000.50,
        'currency' => 'USD', // This should be overridden
        'is_active' => false, // This should be overridden
    ];

    $account = $this->action->execute($data, $this->user);

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('Premium Savings Account')
        ->type->toBe(AccountTypeEnum::CREDIT_CARD)
        ->balance->toBe('5000.50')
        ->currency->toBe('INR') // Should be overridden
        ->is_active->toBeTrue() // Should be overridden
        ->user_id->toBe($this->user->id);
});

test('can execute add account action with cash account type', function () {
    $data = [
        'name' => 'Cash Wallet',
        'type' => AccountTypeEnum::CASH,
        'balance' => 500.00,
    ];

    $account = $this->action->execute($data, $this->user);

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('Cash Wallet')
        ->type->toBe(AccountTypeEnum::CASH)
        ->balance->toBe('500.00')
        ->currency->toBe('INR')
        ->is_active->toBeTrue()
        ->user_id->toBe($this->user->id);
});

test('can execute add account action with zero balance', function () {
    $data = [
        'name' => 'Empty Account',
        'type' => AccountTypeEnum::BANK,
        'balance' => 0.00,
    ];

    $account = $this->action->execute($data, $this->user);

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('Empty Account')
        ->type->toBe(AccountTypeEnum::BANK)
        ->balance->toBe('0.00')
        ->currency->toBe('INR')
        ->is_active->toBeTrue()
        ->user_id->toBe($this->user->id);
});

test('can execute add account action with decimal balance', function () {
    $data = [
        'name' => 'Precision Account',
        'type' => AccountTypeEnum::BANK,
        'balance' => 1234.567,
    ];

    $account = $this->action->execute($data, $this->user);

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('Precision Account')
        ->type->toBe(AccountTypeEnum::BANK)
        ->balance->toBe('1234.57')
        ->currency->toBe('INR')
        ->is_active->toBeTrue()
        ->user_id->toBe($this->user->id);
});

test('can execute add account action with special characters in name', function () {
    $data = [
        'name' => 'Account & Co. (Special) - 2024',
        'type' => AccountTypeEnum::BANK,
        'balance' => 1000.00,
    ];

    $account = $this->action->execute($data, $this->user);

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('Account & Co. (Special) - 2024')
        ->type->toBe(AccountTypeEnum::BANK)
        ->balance->toBe('1000.00')
        ->currency->toBe('INR')
        ->is_active->toBeTrue()
        ->user_id->toBe($this->user->id);
});

test('can execute add account action with different users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $data = [
        'name' => 'User Account',
        'type' => AccountTypeEnum::BANK,
        'balance' => 1000.00,
    ];

    $account1 = $this->action->execute($data, $user1);
    $account2 = $this->action->execute($data, $user2);

    expect($account1)
        ->toBeInstanceOf(Account::class)
        ->user_id->toBe($user1->id);

    expect($account2)
        ->toBeInstanceOf(Account::class)
        ->user_id->toBe($user2->id);

    expect($account1->id)->not->toBe($account2->id);
});

test('can execute add account action multiple times for same user', function () {
    $data1 = [
        'name' => 'First Account',
        'type' => AccountTypeEnum::BANK,
        'balance' => 1000.00,
    ];

    $data2 = [
        'name' => 'Second Account',
        'type' => AccountTypeEnum::CASH,
        'balance' => 500.00,
    ];

    $account1 = $this->action->execute($data1, $this->user);
    $account2 = $this->action->execute($data2, $this->user);

    expect($account1)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('First Account')
        ->user_id->toBe($this->user->id);

    expect($account2)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('Second Account')
        ->user_id->toBe($this->user->id);

    expect($account1->id)->not->toBe($account2->id);
});

test('account is persisted to database after execution', function () {
    $data = [
        'name' => 'Persistent Account',
        'type' => AccountTypeEnum::BANK,
        'balance' => 1000.00,
    ];

    $account = $this->action->execute($data, $this->user);

    expect($account->exists)->toBeTrue();
    expect($account->id)->not->toBeNull();

    // Verify it can be retrieved from database
    $retrievedAccount = Account::find($account->id);
    expect($retrievedAccount)
        ->toBeInstanceOf(Account::class)
        ->name->toBe('Persistent Account')
        ->type->toBe(AccountTypeEnum::BANK)
        ->balance->toBe('1000.00')
        ->currency->toBe('INR')
        ->is_active->toBeTrue()
        ->user_id->toBe($this->user->id);
});

test('action returns same account instance that was created', function () {
    $data = [
        'name' => 'Return Test Account',
        'type' => AccountTypeEnum::BANK,
        'balance' => 1000.00,
    ];

    $account = $this->action->execute($data, $this->user);

    // Verify the returned account is the same instance that was created
    expect($account->exists)->toBeTrue();
    expect($account->wasRecentlyCreated)->toBeTrue();
    // Check that the account has the expected attributes
    expect($account->name)->toBe('Return Test Account');
    expect($account->type)->toBe(AccountTypeEnum::BANK);
    expect($account->balance)->toBe('1000.00');
});
