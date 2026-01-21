<?php

namespace App\Actions;

use App\Enums\TransactionSourceTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class UpdateCreditCardTransactionAction
{
    public function execute(
        Transaction $transaction,
        array $data,
        Account $newAccount,
        Account $oldAccount,
        Category $newCategory,
        float $oldAmount
    ): Transaction {
        return DB::transaction(function () use (
            $transaction,
            $data,
            $newAccount,
            $oldAccount,
            $newCategory,
            $oldAmount
        ) {
            $newAmount = $data['amount'];
            $accountChanged = $oldAccount->id !== $newAccount->id;

            if (! $accountChanged) {
                // Handle same account: reverse old entry, then apply new entry
                $this->handleSameAccountUpdate($oldAccount, $oldAmount, $newAmount);
            } else {
                // Handle different account: reverse on old account, apply on new account
                $this->handleDifferentAccountUpdate($oldAccount, $newAccount, $oldAmount, $newAmount);
            }

            // Update the transaction
            $transaction->fill([
                'account_id' => $newAccount->id,
                'category_id' => $newCategory->id,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'description' => $data['description'],
                'type' => TransactionSourceTypeEnum::CREDIT_CARD->value,
            ]);
            $transaction->save();

            return $transaction;
        });
    }

    private function handleSameAccountUpdate(
        Account $account,
        float $oldAmount,
        float $newAmount,
    ): void {
        // Reverse old entry: add back the old amount to credit_limit
        $account->increment('credit_limit', $oldAmount);
        // Apply new entry: deduct the new amount from credit_limit
        $account->decrement('credit_limit', $newAmount);
    }

    private function handleDifferentAccountUpdate(
        Account $oldAccount,
        Account $newAccount,
        float $oldAmount,
        float $newAmount,
    ): void {
        // Reverse old entry: add back the old amount to old account's credit_limit
        $oldAccount->increment('credit_limit', $oldAmount);
        // Apply new entry: deduct the new amount from new account's credit_limit
        $newAccount->decrement('credit_limit', $newAmount);
    }
}
