<?php

namespace App\Actions;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AddCreditCardTransactionAction
{
    public function execute(array $data, Category $category, Account $account, User $user): Transaction
    {
        Log::info('AddCreditCardTransactionAction executed');

        // TODO: Implement credit card transaction logic
        // For now, this is a placeholder that logs the execution
        throw new \RuntimeException('AddCreditCardTransactionAction not yet implemented');
    }
}
