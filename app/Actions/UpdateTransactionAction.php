<?php

namespace App\Actions;

use App\Enums\AccountTypeEnum;
use App\Enums\TransactionSourceTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class UpdateTransactionAction
{
    public function execute(
        Transaction $transaction,
        array $data,
        Account $newAccount,
        Account $oldAccount,
        Category $newCategory,
        Category $oldCategory,
        float $oldAmount): Transaction
    {
        return DB::transaction(function () use (
            $transaction,
            $data,
            $newAccount,
            $oldAccount,
            $newCategory,
            $oldCategory,
            $oldAmount
        ) {
            $newAmount = $data['amount'];
            $accountChanged = $oldAccount->id !== $newAccount->id;

            if (! $accountChanged) {
                // handle same account amount change
                $this->handleSameAccountUpdate(
                    $oldAccount,
                    $oldAmount,
                    $newAmount,
                    $oldCategory,
                    $newCategory,
                );
            } else {
                // handle different account amount change
                $this->handleDifferentAccountUpdate(
                    $oldAccount,
                    $newAccount,
                    $oldAmount,
                    $newAmount,
                    $oldCategory,
                    $newCategory,
                );
            }
            // Determine type based on account or use provided type
            $type = $data['type'] ?? ($newAccount->type === AccountTypeEnum::CREDIT_CARD
                ? TransactionSourceTypeEnum::CREDIT_CARD->value
                : TransactionSourceTypeEnum::NORMAL->value);

            // Update the transaction
            $transaction->fill([
                'account_id' => $newAccount->id,
                'category_id' => $newCategory->id,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'description' => $data['description'],
                'type' => $type,
            ]);
            $transaction->save();

            return $transaction;
        });
    }

    private function handleSameAccountUpdate(
        Account $account,
        float $oldAmount,
        float $newAmount,
        Category $oldCategory,
        Category $newCategory,
    ) {
        $account->increment('balance', $oldAmount);
        $account->decrement('balance', $newAmount);
    }

    private function handleDifferentAccountUpdate(
        Account $oldAccount,
        Account $newAccount,
        float $oldAmount,
        float $newAmount,
        Category $oldCategory,
        Category $newCategory,
    ) {
        $oldAccount->increment('balance', $oldAmount);
        $newAccount->decrement('balance', $newAmount);
    }
}
