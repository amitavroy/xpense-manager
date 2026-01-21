<?php

use App\Actions\UpdateCreditCardTransactionAction;
use App\Enums\AccountTypeEnum;
use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->action = new UpdateCreditCardTransactionAction;
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 10000.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);
    $this->category = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
});

test('can update credit card transaction with same account and increased amount', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    // Initial credit_limit after transaction creation
    $this->account->refresh();
    $initialCreditLimit = $this->account->credit_limit;

    $data = [
        'amount' => 150.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $this->account,
        $this->account,
        $this->category,
        100.00
    );

    expect($updatedTransaction)
        ->amount->toBe('150.00')
        ->date->format('Y-m-d')->toBe('2024-01-16')
        ->description->toBe('Updated description')
        ->type->toBe(TransactionSourceTypeEnum::CREDIT_CARD);

    // Verify credit_limit: reversed old (100) and applied new (150)
    // Net change: -50 (credit_limit should decrease by 50)
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe(number_format($initialCreditLimit - 50.00, 2, '.', ''));
});

test('can update credit card transaction with same account and decreased amount', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'amount' => 200.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $this->account->refresh();
    $initialCreditLimit = $this->account->credit_limit;

    $data = [
        'amount' => 150.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $this->account,
        $this->account,
        $this->category,
        200.00
    );

    expect($updatedTransaction)
        ->amount->toBe('150.00');

    // Verify credit_limit: reversed old (200) and applied new (150)
    // Net change: +50 (credit_limit should increase by 50)
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe(number_format($initialCreditLimit + 50.00, 2, '.', ''));
});

test('can update credit card transaction with different credit card account', function () {
    $oldAccount = $this->account;
    $newAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 5000.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $oldAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $oldAccount->refresh();
    $newAccount->refresh();
    $oldAccountInitialCreditLimit = $oldAccount->credit_limit;
    $newAccountInitialCreditLimit = $newAccount->credit_limit;

    $data = [
        'amount' => 150.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $newAccount,
        $oldAccount,
        $this->category,
        100.00
    );

    expect($updatedTransaction)
        ->account_id->toBe($newAccount->id)
        ->amount->toBe('150.00')
        ->type->toBe(TransactionSourceTypeEnum::CREDIT_CARD);

    // Verify old account credit_limit: reversed old amount (+100)
    $oldAccount->refresh();
    expect($oldAccount->credit_limit)->toBe(number_format($oldAccountInitialCreditLimit + 100.00, 2, '.', ''));

    // Verify new account credit_limit: applied new amount (-150)
    $newAccount->refresh();
    expect($newAccount->credit_limit)->toBe(number_format($newAccountInitialCreditLimit - 150.00, 2, '.', ''));
});

test('can update credit card transaction with same amount', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $this->account->refresh();
    $initialCreditLimit = $this->account->credit_limit;

    $data = [
        'amount' => 100.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $this->account,
        $this->account,
        $this->category,
        100.00
    );

    expect($updatedTransaction)
        ->amount->toBe('100.00')
        ->description->toBe('Updated description');

    // Verify credit_limit: reversed old (100) and applied new (100) = no net change
    $this->account->refresh();
    expect($this->account->credit_limit)->toBe(number_format($initialCreditLimit, 2, '.', ''));
});

test('can update credit card transaction category', function () {
    $newCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $data = [
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $this->account,
        $this->account,
        $newCategory,
        100.00
    );

    expect($updatedTransaction)
        ->category_id->toBe($newCategory->id);
});

test('credit card transaction type remains credit_card after update', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $data = [
        'amount' => 200.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $this->account,
        $this->account,
        $this->category,
        100.00
    );

    expect($updatedTransaction)
        ->type->toBe(TransactionSourceTypeEnum::CREDIT_CARD);
});
