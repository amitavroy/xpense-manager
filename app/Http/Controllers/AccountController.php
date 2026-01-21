<?php

namespace App\Http\Controllers;

use App\Actions\AddAccountAction;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $accounts = Account::query()
            ->where('user_id', Auth::user()->id)
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('accounts/index', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $account = new Account;

        return Inertia::render('accounts/create', [
            'account' => $account,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccountRequest $request, AddAccountAction $addAccountAction): RedirectResponse
    {
        $data = $request->validated();

        $account = $addAccountAction->execute($data, Auth::user());

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Account created successfully!',
        ]);

        return redirect()->route('accounts.show', $account);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account): Response
    {
        return Inertia::render('accounts/show', [
            'account' => $account,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $data = $request->validated();

        $account->update($data);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Account updated successfully!',
        ]);

        return redirect()->route('accounts.show', $account);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account): RedirectResponse
    {
        abort_if($account->user_id !== Auth::user()->id, 403);

        $account->delete();

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Account deleted successfully!',
        ]);

        return redirect()->route('accounts.index');
    }
}
