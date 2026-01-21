<?php

use App\Enums\AccountTypeEnum;
use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
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

    $this->anotherCreditCardAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 5000.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);

    $this->category = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
});

test('credit card transaction cannot change to non-credit-card account', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Credit card transaction',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $data = [
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Updated description',
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertSessionHasErrors(['account_id']);
    expect($response->getSession()->get('errors')->get('account_id')[0])
        ->toContain('Credit card transactions can only be moved to another credit card account');
});

test('normal transaction cannot change to credit card account', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Normal transaction',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $data = [
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Updated description',
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertSessionHasErrors(['account_id']);
    expect($response->getSession()->get('errors')->get('account_id')[0])
        ->toContain('Transaction type cannot be changed');
});

test('credit card transaction can change to another credit card account', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Credit card transaction',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $data = [
        'account_id' => $this->anotherCreditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 150.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'account_id' => $this->anotherCreditCardAccount->id,
        'amount' => 150.00,
        'type' => TransactionSourceTypeEnum::CREDIT_CARD->value,
    ]);
});

test('credit card transaction amount cannot exceed credit limit', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Credit card transaction',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    // Set credit_limit to a low value
    $this->creditCardAccount->update(['credit_limit' => 50.00]);

    $data = [
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 200.00, // Exceeds credit_limit
        'date' => '2024-01-15',
        'description' => 'Updated description',
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertSessionHasErrors(['amount']);
    $errorMessage = $response->getSession()->get('errors')->get('amount')[0];
    expect($errorMessage)
        ->toContain('credit limit');
});

test('credit card transaction can increase amount within credit limit', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Credit card transaction',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    // Refresh to get current credit_limit after transaction creation
    $this->creditCardAccount->refresh();
    $availableCreditLimit = $this->creditCardAccount->credit_limit;

    // Increase amount but stay within credit limit
    $newAmount = min($availableCreditLimit + 50, 200.00);

    $data = [
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => $newAmount,
        'date' => '2024-01-15',
        'description' => 'Updated description',
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'amount' => (string) $newAmount,
    ]);
});

test('normal transaction validates balance for same account', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Normal transaction',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    // Set balance to a low value
    $this->normalAccount->update(['balance' => 50.00]);

    $data = [
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->category->id,
        'amount' => 200.00, // Would require more balance than available
        'date' => '2024-01-15',
        'description' => 'Updated description',
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertSessionHasErrors(['amount']);
});

test('normal transaction validates balance for different account', function () {
    $anotherNormalAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 50.00,
        'type' => AccountTypeEnum::CASH,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Normal transaction',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $data = [
        'account_id' => $anotherNormalAccount->id,
        'category_id' => $this->category->id,
        'amount' => 200.00, // Exceeds new account balance
        'date' => '2024-01-15',
        'description' => 'Updated description',
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertSessionHasErrors(['amount']);
    expect($response->getSession()->get('errors')->get('amount')[0])
        ->toContain('New account has insufficient balance');
});

test('credit card transaction can decrease amount', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 200.00,
        'date' => '2024-01-15',
        'description' => 'Credit card transaction',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $data = [
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00, // Decreased amount
        'date' => '2024-01-15',
        'description' => 'Updated description',
    ];

    $response = $this->put(route('transactions.update', $transaction), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'amount' => 100.00,
    ]);
});
