<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Account;
use App\Queries\TransactionQuery;
use App\Enums\TransactionTypeEnum;
use Illuminate\Support\Facades\Auth;
use App\Actions\GetPendingBillsAction;

class DashboardController extends Controller
{
    public function __invoke(
        GetPendingBillsAction $getPendingBillsAction,
        TransactionQuery $transactionQuery
    ): Response {
        $transactions = $transactionQuery
            ->recentTransactions()
            ->whereCategoryType(TransactionTypeEnum::EXPENSE)
            ->paginate(10);

        $accounts = Account::query()
            ->where('user_id', Auth::user()->id)
            ->orderBy('name')
            ->paginate(10);

        $pendingBills = $getPendingBillsAction->execute();

        $expenseStats = $transactionQuery->expenseStats();

        return Inertia::render('dashboard', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'pendingBills' => $pendingBills,
            'expenseStats' => $expenseStats,
        ]);
    }
}
