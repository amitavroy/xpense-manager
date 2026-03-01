<?php

namespace App\Http\Controllers;

use App\Queries\Reports\MonthlyExpenseQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function monthlyExpenses(MonthlyExpenseQuery $monthlyExpenseQuery): Response
    {
        $rangeEnd = Carbon::now()->endOfMonth();
        $rangeStart = Carbon::now()->subMonths(2)->startOfMonth();

        $monthlyExpenses = $monthlyExpenseQuery->execute(
            $rangeStart->toDateString(),
            $rangeEnd->toDateString(),
            Auth::id()
        );

        return Inertia::render('reports/monthly-expenses', [
            'monthlyExpenses' => $monthlyExpenses->values()->all(),
        ]);
    }
}
