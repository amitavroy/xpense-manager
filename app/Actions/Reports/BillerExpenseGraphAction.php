<?php

namespace App\Actions\Reports;

use App\Models\Biller;
use App\Queries\Reports\BillerExpenseQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BillerExpenseGraphAction
{
    private const ALLOWED_MONTHS = [1, 2, 3, 6, 12];

    public function __construct(
        private readonly BillerExpenseQuery $billerExpenseQuery
    ) {}

    /**
     * @return array{
     *     billers: array<int, array{id: int, name: string}>,
     *     billerExpenseBillers: array<int, array{id: int, name: string}>,
     *     billerExpenseData: array<int, array<string, float|string>>,
     *     selectedBillerIds: array<int, int>,
     *     billerMonths: int
     * }
     */
    public function execute(Request $request, int $userId): array
    {
        $billersQuery = Biller::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $billers = $billersQuery->values()->all();

        $billerMonths = (int) $request->input('biller_months', 3);
        if (! in_array($billerMonths, self::ALLOWED_MONTHS, true)) {
            $billerMonths = 3;
        }

        $selectedBillerIds = array_map(
            'intval',
            Arr::wrap($request->input('biller_ids', []))
        );
        $allowedBillerIds = $billersQuery->pluck('id')->all();
        $selectedBillerIds = array_values(array_intersect($selectedBillerIds, $allowedBillerIds));

        $billerExpenseBillers = [];
        $billerExpenseData = [];

        if ($selectedBillerIds !== []) {
            $billerRangeEnd = Carbon::now()->subMonth()->endOfMonth();
            $billerRangeStart = $billerRangeEnd->copy()->subMonths($billerMonths - 1)->startOfMonth();

            $result = $this->billerExpenseQuery->execute(
                $billerRangeStart->toDateString(),
                $billerRangeEnd->toDateString(),
                $userId,
                $selectedBillerIds
            );

            $billerExpenseBillers = $result['billers'];
            $billerExpenseData = $result['data'];
        }

        return [
            'billers' => $billers,
            'billerExpenseBillers' => $billerExpenseBillers,
            'billerExpenseData' => $billerExpenseData,
            'selectedBillerIds' => $selectedBillerIds,
            'billerMonths' => $billerMonths,
        ];
    }
}
