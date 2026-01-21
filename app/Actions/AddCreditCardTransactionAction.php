<?php

namespace App\Actions;

use App\Enums\TransactionSourceTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddCreditCardTransactionAction
{
    public function execute(array $data, Category $category, Account $account, User $user): Transaction
    {
        // Validate that the date is not in the future
        $transactionDate = is_string($data['date']) ? Carbon::parse($data['date'])->startOfDay() : $data['date']->startOfDay();
        if ($transactionDate->isAfter(Carbon::today())) {
            throw ValidationException::withMessages([
                'date' => 'Credit card transactions cannot have future dates.',
            ]);
        }

        // Validate that description is not empty
        if (empty(trim($data['description'] ?? ''))) {
            throw ValidationException::withMessages([
                'description' => 'Credit card transactions must have a description.',
            ]);
        }

        return DB::transaction(function () use ($data, $account, $category, $user) {
            $transaction = Transaction::create(attributes: [
                'user_id' => $user->id,
                'account_id' => $account->id,
                'category_id' => $category->id,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'description' => $data['description'],
                'type' => TransactionSourceTypeEnum::CREDIT_CARD->value,
            ]);

            // Credit card transactions are always expenses - decrement credit_limit
            $account->decrement(
                column: 'credit_limit',
                amount: $data['amount']
            );

            return $transaction;
        });
    }
}
