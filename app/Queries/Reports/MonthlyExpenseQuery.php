<?php

namespace App\Queries\Reports;

use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyExpenseQuery
{
    public function execute(string $startDate, string $endDate, int $userId): Collection
    {
        $rangeStart = Carbon::parse($startDate)->copy()->startOfMonth();
        $rangeEnd = Carbon::parse($endDate)->copy()->endOfMonth();

        $months = $this->buildMonthsList($rangeStart, $rangeEnd);
        $aggregates = $this->runAggregatedQuery($rangeStart, $rangeEnd, $userId);

        foreach ($aggregates as $row) {
            $monthKey = $row->month_key;
            $months[$monthKey] = [
                'month' => $monthKey,
                'total' => (float) $row->total,
                'normal' => (float) $row->normal,
                'credit_card' => (float) $row->credit_card,
            ];
        }

        return collect($months)->values();
    }

    /**
     * @return array<string, array{month: string, total: float, normal: float, credit_card: float}>
     */
    private function buildMonthsList(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $months = [];
        $current = $rangeStart->copy();

        while ($current->lte($rangeEnd)) {
            $key = $current->format('Y-m');
            $months[$key] = [
                'month' => $key,
                'total' => 0.0,
                'normal' => 0.0,
                'credit_card' => 0.0,
            ];
            $current->addMonth();
        }

        return $months;
    }

    /**
     * @return \Illuminate\Support\Collection<object{month_key: string, total: string, normal: string, credit_card: string}>
     */
    private function runAggregatedQuery(Carbon $rangeStart, Carbon $rangeEnd, int $userId): Collection
    {
        $driver = DB::connection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', date)"
            : "DATE_FORMAT(date, '%Y-%m')";

        $normalValue = TransactionSourceTypeEnum::NORMAL->value;
        $creditCardValue = TransactionSourceTypeEnum::CREDIT_CARD->value;

        return Transaction::query()
            ->selectRaw("({$monthExpression}) as month_key")
            ->selectRaw('SUM(amount) as total')
            ->selectRaw('SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as normal', [$normalValue])
            ->selectRaw('SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as credit_card', [$creditCardValue])
            ->where('user_id', $userId)
            ->whereCategoryType(TransactionTypeEnum::EXPENSE)
            ->whereIn('type', [TransactionSourceTypeEnum::NORMAL, TransactionSourceTypeEnum::CREDIT_CARD])
            ->whereBetween('date', [$rangeStart, $rangeEnd])
            ->groupByRaw($monthExpression)
            ->get();
    }
}
