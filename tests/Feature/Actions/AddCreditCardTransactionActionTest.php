<?php

use App\Actions\AddCreditCardTransactionAction;
use App\Enums\AccountTypeEnum;
use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->action = new AddCreditCardTransactionAction;
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 10000.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);
});

test('can execute add credit card transaction action for expense', function () {
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
        ->description->toBe('Grocery shopping')
        ->type->toBe(TransactionSourceTypeEnum::CREDIT_CARD);

    // Verify credit_limit was decremented
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe('9899.50');
});

test('can execute add credit card transaction action with zero amount', function () {
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

    // Verify credit_limit remains unchanged
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe('10000.00');
});

test('can execute add credit card transaction action with decimal precision', function () {
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

    // Verify credit_limit was decremented with proper precision
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe('9876.54');
});

test('cannot execute add credit card transaction action with future date', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 50.00,
        'date' => Carbon::tomorrow()->format('Y-m-d'),
        'description' => 'Future transaction',
    ];

    expect(fn () => $this->action->execute($data, $expenseCategory, $this->account, $this->user))
        ->toThrow(\Illuminate\Validation\ValidationException::class);

    // Verify no transaction was created
    expect(Transaction::where('account_id', $this->account->id)->count())->toBe(0);

    // Verify credit_limit was not decremented
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe('10000.00');
});

test('can execute add credit card transaction action with past date', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 75.25,
        'date' => '2020-01-01',
        'description' => 'Past transaction',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->date->format('Y-m-d')->toBe('2020-01-01')
        ->description->toBe('Past transaction');
});

test('can execute add credit card transaction action with long description', function () {
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

test('can execute add credit card transaction action with special characters in description', function () {
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

test('cannot execute add credit card transaction action with empty description', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data = [
        'amount' => 15.00,
        'date' => '2024-01-15',
        'description' => '',
    ];

    expect(fn () => $this->action->execute($data, $expenseCategory, $this->account, $this->user))
        ->toThrow(\Illuminate\Validation\ValidationException::class);

    // Verify no transaction was created
    expect(Transaction::where('account_id', $this->account->id)->count())->toBe(0);

    // Verify credit_limit was not decremented
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe('10000.00');
});

test('can execute multiple credit card transactions for same account', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $data1 = [
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'First expense',
    ];

    $data2 = [
        'amount' => 200.00,
        'date' => '2024-01-16',
        'description' => 'Second expense',
    ];

    $transaction1 = $this->action->execute($data1, $expenseCategory, $this->account, $this->user);
    $transaction2 = $this->action->execute($data2, $expenseCategory, $this->account, $this->user);

    expect($transaction1)
        ->toBeInstanceOf(Transaction::class)
        ->account_id->toBe($this->account->id);

    expect($transaction2)
        ->toBeInstanceOf(Transaction::class)
        ->account_id->toBe($this->account->id);

    expect($transaction1->id)->not->toBe($transaction2->id);

    // Verify final credit_limit
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe('9700.00'); // 10000 - 100 - 200
});

test('can execute credit card transactions for different accounts', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $account2 = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 5000.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);

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

    expect($this->account->credit_limit)->toBe('9950.00');
    expect($account2->credit_limit)->toBe('4950.00');
});

test('can execute credit card transactions for different users', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $user2 = User::factory()->create();
    $account2 = Account::factory()->create([
        'user_id' => $user2->id,
        'credit_limit' => 8000.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);

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

    expect($this->account->credit_limit)->toBe('9925.00');
    expect($account2->credit_limit)->toBe('7925.00');
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
        ->description->toBe('Persistent transaction')
        ->type->toBe(TransactionSourceTypeEnum::CREDIT_CARD);
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
    expect($transaction->type)->toBe(TransactionSourceTypeEnum::CREDIT_CARD);
});

test('credit_limit is decremented correctly for credit card expense', function () {
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $initialCreditLimit = $this->account->credit_limit;

    $data = [
        'amount' => 250.75,
        'date' => '2024-01-15',
        'description' => 'Test credit limit decrement',
    ];

    $transaction = $this->action->execute($data, $expenseCategory, $this->account, $this->user);

    $this->account->refresh();
    $expectedCreditLimit = (float) $initialCreditLimit - 250.75;

    expect($this->account->credit_limit)->toBe((string) $expectedCreditLimit);
    expect($transaction->type)->toBe(TransactionSourceTypeEnum::CREDIT_CARD);
});
