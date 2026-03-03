<?php

namespace App\Http\Controllers;

use App\Actions\Reports\BillerExpenseGraphAction;
use App\Queries\Reports\MonthlyExpenseByCategoryQuery;
use App\Queries\Reports\MonthlyExpenseQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    private const CACHE_TTL_SECONDS = 3600;

    public function monthlyExpenses(
        Request $request,
        MonthlyExpenseQuery $monthlyExpenseQuery,
        MonthlyExpenseByCategoryQuery $monthlyExpenseByCategoryQuery,
        BillerExpenseGraphAction $billerExpenseGraphAction
    ): Response {
        $cacheKey = 'reports.monthly-expenses.'.Auth::id();

        if ($request->boolean('cacheClear')) {
            Cache::forget($cacheKey);
        }

        $rangeEnd = Carbon::now()->endOfMonth();
        $rangeStart = Carbon::now()->subMonths(2)->startOfMonth();

        $data = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($rangeStart, $rangeEnd, $monthlyExpenseQuery, $monthlyExpenseByCategoryQuery) {
            $monthlyExpenses = $monthlyExpenseQuery->execute(
                $rangeStart->toDateString(),
                $rangeEnd->toDateString(),
                Auth::id()
            );

            $monthlyExpensesByCategory = $monthlyExpenseByCategoryQuery->execute(
                $rangeStart->toDateString(),
                $rangeEnd->toDateString(),
                Auth::id()
            );

            return [
                $monthlyExpenses->values()->all(),
                $monthlyExpensesByCategory->values()->all(),
            ];
        });

        // Support both list (current) and associative (legacy) cache format
        $monthlyExpenses = array_is_list($data) ? $data[0] : $data['monthlyExpenses'];
        $monthlyExpensesByCategory = array_is_list($data) ? $data[1] : $data['monthlyExpensesByCategory'];

        $billerGraph = $billerExpenseGraphAction->execute($request, Auth::id());

        return Inertia::render('reports/monthly-expenses', [
            'monthlyExpenses' => $monthlyExpenses,
            'monthlyExpensesByCategory' => $monthlyExpensesByCategory,
            'billers' => $billerGraph['billers'],
            'billerExpenseData' => $billerGraph['billerExpenseData'],
            'billerExpenseBillers' => $billerGraph['billerExpenseBillers'],
            'selectedBillerIds' => $billerGraph['selectedBillerIds'],
            'billerMonths' => $billerGraph['billerMonths'],
        ]);
    }
}
