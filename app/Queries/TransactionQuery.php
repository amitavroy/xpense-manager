<?php

namespace App\Queries;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

class TransactionQuery
{
    public function recentTransactions(): Builder
    {
        return Transaction::query()
            ->with([
                'account',
                'category',
                'user' => function ($query) {
                    $query->select('id', 'name', 'email');
                },
            ])
            ->orderByDesc('date')
            ->orderByDesc('id');
    }
}
