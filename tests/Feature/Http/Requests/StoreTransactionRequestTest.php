<?php

use App\Enums\AccountTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->normalAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 1000.00,
        'type' => AccountTypeEnum::CASH,
    ]);

    $this->creditCardAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 10000.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);

    $this->expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $this->incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);
});

test('account must belong to authenticated user', function () {
    $otherUser = User::factory()->create();
    $otherUserAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
        'balance' => 1000.00,
        'type' => AccountTypeEnum::CASH,
    ]);

    $data = [
        'account_id' => $otherUserAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors(['account_id']);
    expect($response->getSession()->get('errors')->get('account_id')[0])
        ->toBe('Account not found');
});

test('account must exist', function () {
    $data = [
        'account_id' => 99999,
        'category_id' => $this->expenseCategory->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors(['account_id']);
    expect($response->getSession()->get('errors')->get('account_id')[0])
        ->toBe('Account not found');
});

test('category must exist', function () {
    $data = [
        'account_id' => $this->normalAccount->id,
        'category_id' => 99999,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors(['category_id']);
    expect($response->getSession()->get('errors')->get('category_id')[0])
        ->toBe('Category not found');
});

test('expense transaction cannot exceed account balance for non-credit-card accounts', function () {
    $lowBalanceAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 50.00,
        'type' => AccountTypeEnum::CASH,
    ]);

    $data = [
        'account_id' => $lowBalanceAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 200.00, // Exceeds balance
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors(['amount']);
    expect($response->getSession()->get('errors')->get('amount')[0])
        ->toBe('Insufficient balance');
});

test('expense transaction can be created when account has sufficient balance', function () {
    $data = [
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 100.00,
        'description' => 'Test transaction',
    ]);
});

test('expense transaction on credit card account does not validate balance', function () {
    // Credit card accounts don't check balance for expenses
    $data = [
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 50000.00, // Much larger than credit_limit, but balance check is skipped
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    // This should fail on credit limit validation, not balance
    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors(['amount']);
    expect($response->getSession()->get('errors')->get('amount')[0])
        ->toBe('Amount exceeds credit limit');
});

test('income transaction does not validate balance', function () {
    $lowBalanceAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 10.00,
        'type' => AccountTypeEnum::CASH,
    ]);

    $data = [
        'account_id' => $lowBalanceAccount->id,
        'category_id' => $this->incomeCategory->id,
        'amount' => 1000.00, // Large amount, but income doesn't check balance
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'account_id' => $lowBalanceAccount->id,
        'category_id' => $this->incomeCategory->id,
        'amount' => 1000.00,
    ]);
});

test('credit card transaction cannot exceed credit limit', function () {
    $lowCreditLimitAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 50.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);

    $data = [
        'account_id' => $lowCreditLimitAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 200.00, // Exceeds credit_limit
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors(['amount']);
    expect($response->getSession()->get('errors')->get('amount')[0])
        ->toBe('Amount exceeds credit limit');
});

test('credit card transaction can be created within credit limit', function () {
    $data = [
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 100.00,
        'description' => 'Test transaction',
    ]);
});

test('validation fails when both account and category are invalid', function () {
    $data = [
        'account_id' => 99999,
        'category_id' => 99999,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertSessionHasErrors(['account_id', 'category_id']);
});

test('expense transaction with exact balance amount is valid', function () {
    $exactBalanceAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 100.00,
        'type' => AccountTypeEnum::CASH,
    ]);

    $data = [
        'account_id' => $exactBalanceAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 100.00, // Exactly the balance
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'account_id' => $exactBalanceAccount->id,
        'amount' => 100.00,
    ]);
});

test('credit card transaction with exact credit limit amount is valid', function () {
    $exactCreditLimitAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 100.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);

    $data = [
        'account_id' => $exactCreditLimitAccount->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 100.00, // Exactly the credit_limit
        'date' => '2024-01-15',
        'description' => 'Test transaction',
    ];

    $response = $this->post(route('transactions.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'account_id' => $exactCreditLimitAccount->id,
        'amount' => 100.00,
    ]);
});
