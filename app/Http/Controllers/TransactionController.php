<?php

namespace App\Http\Controllers;

use App\Actions\AddTransactionAction;
use App\Actions\UpdateTransactionAction;
use App\Enums\TransactionTypeEnum;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Queries\TransactionQuery;
use App\Services\DropdownService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
        private readonly DropdownService $dropdownService,
        private readonly TransactionQuery $transactionQuery
    ) {}

    public function index(): Response
    {
        $userId = request()->integer('user_id', 0) ?: null;
        $userIdsParam = request()->get('user_ids');
        $fromDate = request()->string('from_date', '');
        $toDate = request()->string('to_date', '');
        $preset = request()->string('preset', '');

        // Normalize empty strings to null
        $fromDate = $fromDate === '' ? null : $fromDate;
        $toDate = $toDate === '' ? null : $toDate;
        $preset = $preset === '' ? null : $preset;

        // Convert user_ids array to integers and filter out empty values
        // Handle both array format and single value
        $userIds = [];
        if ($userIdsParam !== null) {
            $userIds = is_array($userIdsParam) ? $userIdsParam : [$userIdsParam];
        }
        $userIds = array_filter(array_map('intval', $userIds));
        $userIds = count($userIds) > 0 ? array_values($userIds) : null;

        $transactions = $this->transactionQuery
            ->expenses(
                userId: $userId,
                userIds: $userIds,
                fromDate: $fromDate,
                toDate: $toDate,
                preset: $preset
            )
            ->paginate(10)
            ->withQueryString();

        $users = $this->dropdownService->getUsers();

        return Inertia::render('transactions/index', [
            'transactions' => $transactions,
            'users' => $users,
            'filters' => [
                'user_id' => $userId,
                'user_ids' => $userIds ?? [],
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'preset' => $preset,
            ],
        ]);
    }

    public function show(Transaction $transaction): Response
    {
        $accounts = $this->dropdownService->getAccounts(Auth::user());
        $categories = $this->dropdownService->getCategories(TransactionTypeEnum::EXPENSE);

        return Inertia::render('transactions/show', [
            'accounts' => $accounts,
            'categories' => $categories,
            'transaction' => $transaction,
        ]);
    }

    public function update(
        UpdateTransactionRequest $request,
        Transaction $transaction,
        UpdateTransactionAction $action
    ): RedirectResponse {
        $data = $request->validated();

        // Pull the data from context
        $oldAccount = Context::pull('old_account');
        $newAccount = Context::pull('new_account');
        $oldCategory = Context::pull('old_category');
        $newCategory = Context::pull('new_category');
        $oldAmount = Context::pull('old_amount');

        $action->execute(
            transaction: $transaction,
            data: $data,
            newAccount: $newAccount,
            oldAccount: $oldAccount,
            newCategory: $newCategory,
            oldCategory: $oldCategory,
            oldAmount: $oldAmount,
        );

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Transaction updated successfully!',
        ]);

        return redirect()->route('transactions.show', $transaction);
    }

    public function create(): Response
    {
        $accounts = $this->dropdownService->getAccounts(Auth::user());
        $categories = $this->dropdownService
            ->getCategories(TransactionTypeEnum::EXPENSE);
        $transaction = new Transaction;

        return Inertia::render('transactions/create', [
            'accounts' => $accounts,
            'categories' => $categories,
            'transaction' => $transaction,
        ]);
    }

    public function store(
        StoreTransactionRequest $request,
        AddTransactionAction $action
    ): RedirectResponse {
        $user = Auth::user();
        $data = $request->validated();

        $category = Context::pull('category');
        $account = Context::pull('account');

        // create the transaction
        $transaction = $action->execute($data, $category, $account, $user);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Transaction created successfully!',
        ]);

        return redirect()->route('transactions.show', $transaction);
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->load(['account', 'category']);
        abort_if($transaction->user_id !== Auth::user()->id, 403);

        if (
            $transaction->category->type === TransactionTypeEnum::INCOME &&
            $transaction->account->balance < $transaction->amount
        ) {
            abort(403, 'Insufficient funds');
        }

        DB::transaction(function () use ($transaction) {
            match ($transaction->category->type) {
                TransactionTypeEnum::EXPENSE => $transaction->account->increment('balance', $transaction->amount),
                TransactionTypeEnum::INCOME => $transaction->account->decrement('balance', $transaction->amount),
            };

            $transaction->delete();
        });

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Transaction deleted successfully!',
        ]);

        return redirect()->route('transactions.index');
    }
}
