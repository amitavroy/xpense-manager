<?php

namespace App\Http\Controllers;

use App\Actions\GetPendingBillsAction;
use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Queries\TransactionQuery;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        GetPendingBillsAction $getPendingBillsAction,
        TransactionQuery $transactionQuery
    ): Response {
        $transactions = $transactionQuery
            ->recentTransactions(type: TransactionSourceTypeEnum::NORMAL)
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
