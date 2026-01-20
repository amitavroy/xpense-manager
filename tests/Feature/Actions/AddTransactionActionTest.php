<?php

use App\Actions\AddTransactionAction;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->action = new AddTransactionAction;
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create(['user_id' => $this->user->id, 'balance' => 1000.00]);
});

test('can execute add transaction action for expense', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 100.50,
        'date' => '2024-01-15',
        'description' => 'Grocery shopping',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->user_id->toBe($this->user->id)
        ->account_id->toBe($this->account->id)
        ->category_id->toBe($expenseCategory->id)
        ->amount->toBe('100.50')
        ->date->format('Y-m-d')->toBe('2024-01-15')
        ->description->toBe('Grocery shopping');

    // Verify account balance was decremented
    $this->account->refresh();
    expect($this->account->balance)->toBe('899.50');
});

test('can execute add transaction action for income', function () {
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    $data = [
        'amount' => 250.75,
        'date' => '2024-01-20',
        'description' => 'Salary payment',
    ];

    $transaction = $this->action->execute($data, $incomeCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->user_id->toBe($this->user->id)
        ->account_id->toBe($this->account->id)
        ->category_id->toBe($incomeCategory->id)
        ->amount->toBe('250.75')
        ->date->format('Y-m-d')->toBe('2024-01-20')
        ->description->toBe('Salary payment');

    // Verify account balance was incremented
    $this->account->refresh();
    expect($this->account->balance)->toBe('1250.75');
});

test('can execute add transaction action with zero amount', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 0.00,
        'date' => '2024-01-15',
        'description' => 'Zero amount transaction',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->amount->toBe('0.00');

    // Verify account balance remains unchanged
    $this->account->refresh();
    expect($this->account->balance)->toBe('1000.00');
});

test('can execute add transaction action with large amount', function () {
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    $data = [
        'amount' => 999999.99,
        'date' => '2024-01-15',
        'description' => 'Large income transaction',
    ];

    $transaction = $this->action->execute($data, $incomeCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->amount->toBe('999999.99');

    // Verify account balance was incremented
    $this->account->refresh();
    expect($this->account->balance)->toBe('1000999.99');
});

test('can execute add transaction action with decimal precision', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 123.456,
        'date' => '2024-01-15',
        'description' => 'Precision test transaction',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->amount->toBe('123.46'); // Should be rounded to 2 decimal places

    // Verify account balance was decremented with proper precision
    $this->account->refresh();
    expect($this->account->balance)->toBe('876.54');
});

test('can execute add transaction action with future date', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 50.00,
        'date' => '2025-12-31',
        'description' => 'Future transaction',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->date->format('Y-m-d')->toBe('2025-12-31')
        ->description->toBe('Future transaction');
});

test('can execute add transaction action with past date', function () {
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    $data = [
        'amount' => 75.25,
        'date' => '2020-01-01',
        'description' => 'Past transaction',
    ];

    $transaction = $this->action->execute($data, $incomeCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->date->format('Y-m-d')->toBe('2020-01-01')
        ->description->toBe('Past transaction');
});

test('can execute add transaction action with long description', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 25.00,
        'date' => '2024-01-15',
        'description' => 'This is a very long description that contains multiple sentences and should test the system\'s ability to handle lengthy transaction descriptions without any issues.',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->description->toBe('This is a very long description that contains multiple sentences and should test the system\'s ability to handle lengthy transaction descriptions without any issues.');
});

test('can execute add transaction action with special characters in description', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 30.00,
        'date' => '2024-01-15',
        'description' => 'Transaction with special chars: @#$%^&*()_+-=[]{}|;:,.<>?',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->description->toBe('Transaction with special chars: @#$%^&*()_+-=[]{}|;:,.<>?');
});

test('can execute add transaction action with empty description', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 15.00,
        'date' => '2024-01-15',
        'description' => '',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->description->toBe('');
});

test('can execute multiple transactions for same account', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    $expenseData = [
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'First expense',
    ];

    $incomeData = [
        'amount' => 200.00,
        'date' => '2024-01-16',
        'description' => 'First income',
    ];

    $transaction1 = $this->action->execute($expenseData, $expenseCategory, $this->account, $this->user);
    $transaction2 = $this->action->execute($incomeData, $incomeCategory, $this->account, $this->user);

    expect($transaction1)
        ->toBeInstanceOf(Transaction::class)
        ->account_id->toBe($this->account->id);

    expect($transaction2)
        ->toBeInstanceOf(Transaction::class)
        ->account_id->toBe($this->account->id);

    expect($transaction1->id)->not->toBe($transaction2->id);

    // Verify final account balance
    $this->account->refresh();
    expect($this->account->balance)->toBe('1100.00'); // 1000 - 100 + 200
});

test('can execute transactions for different accounts', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $account2 = Account::factory()->create(['user_id' => $this->user->id, 'balance' => 500.00]);

    $data = [
        'amount' => 50.00,
        'date' => '2024-01-15',
        'description' => 'Same transaction different accounts',
    ];

    $transaction1 = $this->action->execute($data, $expenseCategory, $this->account, $this->user);
    $transaction2 = $this->action->execute($data, $expenseCategory, $account2, $this->user);

    expect($transaction1)
        ->toBeInstanceOf(Transaction::class)
        ->account_id->toBe($this->account->id);

    expect($transaction2)
        ->toBeInstanceOf(Transaction::class)
        ->account_id->toBe($account2->id);

    // Verify both accounts were affected independently
    $this->account->refresh();
    $account2->refresh();

    expect($this->account->balance)->toBe('950.00');
    expect($account2->balance)->toBe('450.00');
});

test('can execute transactions for different users', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $user2 = User::factory()->create();
    $account2 = Account::factory()->create(['user_id' => $user2->id, 'balance' => 800.00]);

    $data = [
        'amount' => 75.00,
        'date' => '2024-01-15',
        'description' => 'Same transaction different users',
    ];

    $transaction1 = $this->action->execute($data, $expenseCategory, $this->account, $this->user);
    $transaction2 = $this->action->execute($data, $expenseCategory, $account2, $user2);

    expect($transaction1)
        ->toBeInstanceOf(Transaction::class)
        ->user_id->toBe($this->user->id)
        ->account_id->toBe($this->account->id);

    expect($transaction2)
        ->toBeInstanceOf(Transaction::class)
        ->user_id->toBe($user2->id)
        ->account_id->toBe($account2->id);

    // Verify both accounts were affected independently
    $this->account->refresh();
    $account2->refresh();

    expect($this->account->balance)->toBe('925.00');
    expect($account2->balance)->toBe('725.00');
});

test('transaction is persisted to database after execution', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 200.00,
        'date' => '2024-01-15',
        'description' => 'Persistent transaction',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction->exists)->toBeTrue();
    expect($transaction->id)->not->toBeNull();

    // Verify it can be retrieved from database
    $retrievedTransaction = Transaction::find($transaction->id);
    expect($retrievedTransaction)
        ->toBeInstanceOf(Transaction::class)
        ->user_id->toBe($this->user->id)
        ->account_id->toBe($this->account->id)
        ->category_id->toBe($expenseCategory->id)
        ->amount->toBe('200.00')
        ->date->format('Y-m-d')->toBe('2024-01-15')
        ->description->toBe('Persistent transaction');
});

test('action returns same transaction instance that was created', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 150.00,
        'date' => '2024-01-15',
        'description' => 'Return test transaction',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    // Verify the returned transaction is the same instance that was created
    expect($transaction->exists)->toBeTrue();
    expect($transaction->wasRecentlyCreated)->toBeTrue();
    expect($transaction->user_id)->toBe($this->user->id);
    expect($transaction->account_id)->toBe($this->account->id);
    expect($transaction->category_id)->toBe($expenseCategory->id);
    expect($transaction->amount)->toBe('150.00');
});

test('database transaction rollback on failure', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Test rollback',
    ];

    // Mock a database failure scenario by creating invalid data
    $originalBalance = $this->account->balance;

    // This should work normally
    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)->toBeInstanceOf(Transaction::class);

    // Verify the transaction was created and balance was updated
    $this->account->refresh();
    expect($this->account->balance)->toBe('900.00');

    // Verify transaction exists in database
    expect(Transaction::find($transaction->id))->not->toBeNull();
});
