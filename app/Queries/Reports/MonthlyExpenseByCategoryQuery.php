<?php

namespace App\Queries\Reports;

use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyExpenseByCategoryQuery
{
    /**
     * @return Collection<int, array{month: string, categories: array<int, array{category_id: int, category_name: string, total: float}>}>
     */
    public function execute(string $startDate, string $endDate, int $userId): Collection
    {
        $rangeStart = Carbon::parse($startDate)->copy()->startOfMonth();
        $rangeEnd = Carbon::parse($endDate)->copy()->endOfMonth();

        $months = $this->buildMonthsList($rangeStart, $rangeEnd);
        $aggregates = $this->runAggregatedQuery($rangeStart, $rangeEnd, $userId);

        foreach ($aggregates as $row) {
            $monthKey = $row->month_key;
            if (! isset($months[$monthKey]['categories'])) {
                $months[$monthKey]['categories'] = [];
            }
            $months[$monthKey]['categories'][] = [
                'category_id' => (int) $row->category_id,
                'category_name' => $row->category_name,
                'total' => (float) $row->total,
            ];
        }

        return collect($months)->values();
    }

    /**
     * @return array<string, array{month: string, categories: array}>
     */
    private function buildMonthsList(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $months = [];
        $current = $rangeStart->copy();

        while ($current->lte($rangeEnd)) {
            $key = $current->format('Y-m');
            $months[$key] = [
                'month' => $key,
                'categories' => [],
            ];
            $current->addMonth();
        }

        return $months;
    }

    /**
     * @return \Illuminate\Support\Collection<object{month_key: string, category_id: int, category_name: string, total: string}>
     */
    private function runAggregatedQuery(Carbon $rangeStart, Carbon $rangeEnd, int $userId): Collection
    {
        $driver = DB::connection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', transactions.date)"
            : "DATE_FORMAT(transactions.date, '%Y-%m')";

        $expenseValue = TransactionTypeEnum::EXPENSE->value;

        return Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', $expenseValue)
            ->where('transactions.user_id', $userId)
            ->whereIn('transactions.type', [TransactionSourceTypeEnum::NORMAL, TransactionSourceTypeEnum::CREDIT_CARD])
            ->whereBetween('transactions.date', [$rangeStart, $rangeEnd])
            ->selectRaw("({$monthExpression}) as month_key")
            ->selectRaw('categories.id as category_id')
            ->selectRaw('categories.name as category_name')
            ->selectRaw('SUM(transactions.amount) as total')
            ->groupByRaw($monthExpression)
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }
}
