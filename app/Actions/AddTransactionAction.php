<?php

namespace App\Actions;

use App\Enums\AccountTypeEnum;
use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddTransactionAction
{
    public function __construct(
        private readonly AddCreditCardTransactionAction $addCreditCardTransactionAction
    ) {}

    public function execute(array $data, Category $category, Account $account, User $user): Transaction
    {
        if ($account->type === AccountTypeEnum::CREDIT_CARD) {
            return $this->addCreditCardTransactionAction->execute($data, $category, $account, $user);
        }

        $transaction = DB::transaction(function () use ($data, $account, $category, $user) {
            $type = $data['type'] ?? ($account->type === AccountTypeEnum::CREDIT_CARD
                ? TransactionSourceTypeEnum::CREDIT_CARD
                : TransactionSourceTypeEnum::NORMAL);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'account_id' => $account->id,
                'category_id' => $category->id,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'description' => $data['description'],
                'type' => $type instanceof TransactionSourceTypeEnum ? $type->value : $type,
            ]);

            match ($category->type) {
                TransactionTypeEnum::EXPENSE => $account->decrement('balance', $data['amount']),
                TransactionTypeEnum::INCOME => $account->increment('balance', $data['amount']),
            };

            return $transaction;
        });

        return $transaction;
    }
}
