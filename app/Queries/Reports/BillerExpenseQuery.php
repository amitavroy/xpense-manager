<?php

namespace App\Queries\Reports;

use App\Models\BillInstance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillerExpenseQuery
{
    /**
     * @param  array<int, int>  $billerIds
     * @return array{
     *     billers: array<int, array{id: int, name: string}>,
     *     data: array<int, array<string, float|string>>
     * }
     */
    public function execute(string $startDate, string $endDate, int $userId, array $billerIds): array
    {
        if ($billerIds === []) {
            return [
                'billers' => [],
                'data' => [],
            ];
        }

        $rangeStart = Carbon::parse($startDate)->copy()->startOfMonth();
        $rangeEnd = Carbon::parse($endDate)->copy()->endOfMonth();

        $months = $this->buildMonthsList($rangeStart, $rangeEnd);
        $aggregates = $this->runAggregatedQuery($rangeStart, $rangeEnd, $userId, $billerIds);

        $billers = [];

        foreach ($aggregates as $row) {
            $billerId = (int) $row->biller_id;
            $monthKey = $row->month_key;

            $billers[$billerId] = [
                'id' => $billerId,
                'name' => $row->biller_name,
            ];

            if (! isset($months[$monthKey])) {
                continue;
            }

            $billerKey = $this->billerKey($billerId);

            if (! array_key_exists($billerKey, $months[$monthKey])) {
                $months[$monthKey][$billerKey] = 0.0;
            }

            $months[$monthKey][$billerKey] = (float) $row->total;
        }

        foreach ($months as $monthKey => $monthData) {
            foreach ($billers as $biller) {
                $billerKey = $this->billerKey($biller['id']);

                if (! array_key_exists($billerKey, $monthData)) {
                    $months[$monthKey][$billerKey] = 0.0;
                }
            }
        }

        return [
            'billers' => array_values($billers),
            'data' => array_values($months),
        ];
    }

    /**
     * @return array<string, array<string, float|string>>
     */
    private function buildMonthsList(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $months = [];
        $current = $rangeStart->copy();

        while ($current->lte($rangeEnd)) {
            $key = $current->format('Y-m');
            $months[$key] = [
                'month' => $key,
            ];
            $current->addMonth();
        }

        return $months;
    }

    /**
     * @param  array<int, int>  $billerIds
     * @return \Illuminate\Support\Collection<object{
     *     month_key: string,
     *     biller_id: int,
     *     biller_name: string,
     *     total: string
     * }>
     */
    private function runAggregatedQuery(Carbon $rangeStart, Carbon $rangeEnd, int $userId, array $billerIds): Collection
    {
        $driver = DB::connection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', bill_instances.paid_date)"
            : "DATE_FORMAT(bill_instances.paid_date, '%Y-%m')";

        return BillInstance::query()
            ->join('bills', 'bill_instances.bill_id', '=', 'bills.id')
            ->join('billers', 'bills.biller_id', '=', 'billers.id')
            ->where('bills.user_id', $userId)
            ->where('billers.is_active', true)
            ->whereNotNull('bill_instances.paid_date')
            ->whereIn('billers.id', $billerIds)
            ->whereBetween('bill_instances.paid_date', [$rangeStart, $rangeEnd])
            ->selectRaw("({$monthExpression}) as month_key")
            ->selectRaw('billers.id as biller_id')
            ->selectRaw('billers.name as biller_name')
            ->selectRaw('SUM(bill_instances.amount) as total')
            ->groupByRaw($monthExpression)
            ->groupBy('billers.id', 'billers.name')
            ->orderBy('month_key')
            ->orderBy('biller_id')
            ->get();
    }

    private function billerKey(int $billerId): string
    {
        return 'biller_'.$billerId;
    }
}
