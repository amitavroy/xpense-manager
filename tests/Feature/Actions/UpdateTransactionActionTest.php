<?php

use App\Actions\UpdateCreditCardTransactionAction;
use App\Actions\UpdateTransactionAction;
use App\Enums\AccountTypeEnum;
use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->updateCreditCardAction = new UpdateCreditCardTransactionAction;
    $this->action = new UpdateTransactionAction($this->updateCreditCardAction);
    $this->user = User::factory()->create();
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
    $this->category = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
});

test('delegates credit card transaction update to UpdateCreditCardTransactionAction', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $this->creditCardAccount->refresh();
    $initialCreditLimit = $this->creditCardAccount->credit_limit;
    $initialBalance = $this->creditCardAccount->balance;

    $data = [
        'amount' => 150.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $this->creditCardAccount,
        $this->creditCardAccount,
        $this->category,
        $this->category,
        100.00
    );

    expect($updatedTransaction)
        ->amount->toBe('150.00')
        ->type->toBe(TransactionSourceTypeEnum::CREDIT_CARD);

    // Verify credit_limit was updated (not balance)
    $this->creditCardAccount->refresh();
    expect($this->creditCardAccount->credit_limit)->toBe(number_format($initialCreditLimit - 50.00, 2, '.', ''));
    expect($this->creditCardAccount->balance)->toBe(number_format($initialBalance, 2, '.', '')); // Balance should remain unchanged
});

test('handles normal transaction update with balance logic', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $this->normalAccount->refresh();
    $initialBalance = $this->normalAccount->balance;

    $data = [
        'amount' => 150.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $this->normalAccount,
        $this->normalAccount,
        $this->category,
        $this->category,
        100.00
    );

    expect($updatedTransaction)
        ->amount->toBe('150.00')
        ->type->toBe(TransactionSourceTypeEnum::NORMAL);

    // Verify balance was updated (not credit_limit)
    $this->normalAccount->refresh();
    expect($this->normalAccount->balance)->toBe(number_format($initialBalance - 50.00, 2, '.', ''));
});

test('normal transaction remains normal when updated', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->normalAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $data = [
        'amount' => 200.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $this->normalAccount,
        $this->normalAccount,
        $this->category,
        $this->category,
        100.00
    );

    expect($updatedTransaction)
        ->type->toBe(TransactionSourceTypeEnum::NORMAL);
});

test('credit card transaction can be moved to different credit card account', function () {
    $newCreditCardAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'credit_limit' => 5000.00,
        'type' => AccountTypeEnum::CREDIT_CARD,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->creditCardAccount->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
        'description' => 'Original description',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $this->creditCardAccount->refresh();
    $newCreditCardAccount->refresh();
    $oldAccountInitialCreditLimit = $this->creditCardAccount->credit_limit;
    $newAccountInitialCreditLimit = $newCreditCardAccount->credit_limit;

    $data = [
        'amount' => 150.00,
        'date' => '2024-01-16',
        'description' => 'Updated description',
    ];

    $updatedTransaction = $this->action->execute(
        $transaction,
        $data,
        $newCreditCardAccount,
        $this->creditCardAccount,
        $this->category,
        $this->category,
        100.00
    );

    expect($updatedTransaction)
        ->account_id->toBe($newCreditCardAccount->id)
        ->type->toBe(TransactionSourceTypeEnum::CREDIT_CARD);

    // Verify credit_limit updates on both accounts
    $this->creditCardAccount->refresh();
    $newCreditCardAccount->refresh();
    expect($this->creditCardAccount->credit_limit)->toBe(number_format($oldAccountInitialCreditLimit + 100.00, 2, '.', ''));
    expect($newCreditCardAccount->credit_limit)->toBe(number_format($newAccountInitialCreditLimit - 150.00, 2, '.', ''));
});
