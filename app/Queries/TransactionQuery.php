<?php

namespace App\Queries;

use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

    public function expenseStats(): Collection
    {
        // get current month total expense
        $currentMonthTotalExpense = $this->getTotalExpenseForMonth(
            month: Carbon::now()
        );

        // get previous month total expense
        $previousMonthTotalExpense = $this->getTotalExpenseForMonth(
            month: Carbon::now()->subMonth()
        );

        return collect([
            'currentMonthTotalExpense' => $currentMonthTotalExpense,
            'previousMonthTotalExpense' => $previousMonthTotalExpense,
        ]);
    }

    private function getTotalExpenseForMonth(Carbon|string $month): float
    {
        $monthDate = is_string($month) ? Carbon::parse($month) : $month;

        $startDate = $monthDate->copy()->startOfMonth();
        $endDate = $monthDate->copy()->endOfMonth();

        return Transaction::query()
            ->whereCategoryType(TransactionTypeEnum::EXPENSE)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount') ?? 0.0;
    }
}
