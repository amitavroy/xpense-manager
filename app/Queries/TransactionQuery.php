<?php

namespace App\Queries;

use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransactionQuery
{
    public function recentTransactions(
        TransactionSourceTypeEnum $type = TransactionSourceTypeEnum::NORMAL
    ): Builder {
        return Transaction::query()
            ->where('type', $type)
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

    public function incomes(): Builder
    {
        return Transaction::query()
            ->with(['account', 'category'])
            ->whereCategoryType(TransactionTypeEnum::INCOME)
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

        $currentMonthTotalCreditCardExpense = $this->getTotalCreditCardExpenseForMonth(
            month: Carbon::now()
        );

        $previousMonthTotalCreditCardExpense = $this->getTotalCreditCardExpenseForMonth(
            month: Carbon::now()->subMonth()
        );

        return collect([
            'currentMonthTotalExpense' => $currentMonthTotalExpense,
            'previousMonthTotalExpense' => $previousMonthTotalExpense,
            'currentMonthTotalCreditCardExpense' => $currentMonthTotalCreditCardExpense,
            'previousMonthTotalCreditCardExpense' => $previousMonthTotalCreditCardExpense,
        ]);
    }

    public function expenses(
        ?int $userId = null,
        ?array $userIds = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        ?string $preset = null,
        TransactionSourceTypeEnum $type = TransactionSourceTypeEnum::NORMAL
    ): Builder {
        $query = Transaction::query()
            ->where('type', $type)
            ->with([
                'account',
                'category',
                'user' => function ($query) {
                    $query->select('id', 'name', 'email');
                },
            ])
            ->whereCategoryType(TransactionTypeEnum::EXPENSE);

        // Apply user filter if provided (support both single user_id and multiple user_ids)
        if ($userIds !== null && count($userIds) > 0) {
            $query->whereIn('user_id', $userIds);
        } elseif ($userId !== null) {
            $query->where('user_id', $userId);
        }

        // Handle preset logic (preset overrides from_date/to_date)
        if ($preset !== null) {
            [$fromDate, $toDate] = $this->getPresetDates($preset);
        }

        // Apply date range filter
        $dateRange = $this->getDateRange($fromDate, $toDate);
        $query->whereBetween('date', [$dateRange['from'], $dateRange['to']]);

        return $query->orderByDesc('date')->orderByDesc('id');
    }

    private function getPresetDates(string $preset): array
    {
        $now = Carbon::now();

        return match ($preset) {
            'last_30_days' => [
                $now->copy()->subDays(29)->startOfDay()->format('Y-m-d'),
                $now->copy()->endOfDay()->format('Y-m-d'),
            ],
            'this_month' => [
                $now->copy()->startOfMonth()->format('Y-m-d'),
                $now->copy()->endOfDay()->format('Y-m-d'),
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth()->format('Y-m-d'),
                $now->copy()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'last_week' => [
                $now->copy()->subDays(6)->startOfDay()->format('Y-m-d'),
                $now->copy()->endOfDay()->format('Y-m-d'),
            ],
            default => [
                $now->copy()->startOfMonth()->format('Y-m-d'),
                $now->copy()->endOfMonth()->format('Y-m-d'),
            ],
        };
    }

    private function getDateRange(?string $fromDate, ?string $toDate): array
    {
        $now = Carbon::now();

        // Default to current month if no dates provided
        if ($fromDate === null && $toDate === null) {
            return [
                'from' => $now->copy()->startOfMonth(),
                'to' => $now->copy()->endOfMonth(),
            ];
        }

        // Parse dates
        $from = $fromDate !== null ? Carbon::parse($fromDate)->startOfDay() : $now->copy()->startOfMonth();
        $to = $toDate !== null ? Carbon::parse($toDate)->endOfDay() : $now->copy()->endOfMonth();

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    private function getTotalExpenseForMonth(Carbon|string $month): float
    {
        $monthDate = is_string($month) ? Carbon::parse($month) : $month;

        $startDate = $monthDate->copy()->startOfMonth();
        $endDate = $monthDate->copy()->endOfMonth();

        return Transaction::query()
            ->normal()
            ->whereCategoryType(TransactionTypeEnum::EXPENSE)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount') ?? 0.0;
    }

    private function getTotalCreditCardExpenseForMonth(Carbon|string $month): float
    {
        $monthDate = is_string($month) ? Carbon::parse($month) : $month;

        $startDate = $monthDate->copy()->startOfMonth();
        $endDate = $monthDate->copy()->endOfMonth();

        return Transaction::query()
            ->creditCard()
            ->whereCategoryType(TransactionTypeEnum::EXPENSE)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount') ?? 0.0;
    }
}
