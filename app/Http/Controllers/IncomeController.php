<?php

namespace App\Http\Controllers;

use App\Actions\AddTransactionAction;
use App\Enums\TransactionTypeEnum;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Services\DropdownService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Inertia\Inertia;
use Inertia\Response;

class IncomeController extends Controller
{
    public function __construct(
        private readonly DropdownService $dropdownService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $incomes = Transaction::query()
            ->with(['account', 'category'])
            ->whereHas('category', function ($query) {
                $query->where('type', TransactionTypeEnum::INCOME);
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10);

        return Inertia::render('incomes/index', [
            'incomes' => $incomes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $accounts = $this->dropdownService->getAccounts(Auth::user());
        $categories = $this->dropdownService
            ->getCategories(TransactionTypeEnum::INCOME);
        $transaction = new Transaction;

        return Inertia::render('incomes/create', [
            'accounts' => $accounts,
            'categories' => $categories,
            'transaction' => $transaction,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request, AddTransactionAction $addTransactionAction)
    {
        $user = Auth::user();
        $data = $request->validated();

        $category = Context::pull('category');
        $account = Context::pull('account');

        $transaction = $addTransactionAction->execute($data, $category, $account, $user);

        return redirect()->route('incomes.show', $transaction);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): Response
    {
        $accounts = $this->dropdownService->getAccounts(Auth::user());
        $categories = $this->dropdownService->getCategories(TransactionTypeEnum::INCOME);

        return Inertia::render('incomes/show', [
            'accounts' => $accounts,
            'categories' => $categories,
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
