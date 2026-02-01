<?php

namespace App\Actions;

use App\Enums\TransactionSourceTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AccountReconcileAction
{
    public function execute(Account $account, float $actualBalance): ?Transaction
    {
        $difference = $actualBalance - (float) $account->balance;

        if ($difference == 0) {
            return null;
        }

        $isIncome = $difference > 0;
        $categoryName = $isIncome ? 'Reconciliation-Inc' : 'Reconciliation-Exp';

        $category = Category::where('name', $categoryName)->firstOrFail();

        return DB::transaction(function () use ($account, $category, $difference, $isIncome) {
            $amount = abs($difference);

            $transaction = Transaction::create([
                'user_id' => $account->user_id,
                'account_id' => $account->id,
                'category_id' => $category->id,
                'amount' => $amount,
                'date' => now()->toDateString(),
                'description' => sprintf(
                    'Balance reconciliation: %s%.2f',
                    $isIncome ? '+' : '-',
                    $amount
                ),
                'type' => TransactionSourceTypeEnum::RECONCILIATION->value,
            ]);

            if ($isIncome) {
                $account->increment('balance', $amount);
            } else {
                $account->decrement('balance', $amount);
            }

            return $transaction;
        });
    }
}
