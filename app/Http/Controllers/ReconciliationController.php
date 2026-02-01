<?php

namespace App\Http\Controllers;

use App\Actions\AccountReconcileAction;
use App\Http\Requests\ReconcileAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Context;
use Inertia\Inertia;

class ReconciliationController extends Controller
{
    public function __invoke(ReconcileAccountRequest $request, AccountReconcileAction $accountReconcileAction): RedirectResponse
    {
        $account = Context::pull('account');
        $actualBalance = (float) $request->validated('actual_balance');

        $transaction = $accountReconcileAction->execute($account, $actualBalance);

        if ($transaction) {
            Inertia::flash('notification', [
                'type' => 'success',
                'message' => 'Balance reconciled successfully.',
            ]);
        } else {
            Inertia::flash('notification', [
                'type' => 'info',
                'message' => 'Balance matches — no adjustment needed.',
            ]);
        }

        return redirect()->back();
    }
}
