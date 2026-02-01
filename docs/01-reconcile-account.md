# Balance Reconciliation Feature Implementation Plan

## Overview
Add a "Reconcile Balance" feature to sync account balances when the tracked balance differs from the actual bank balance. The system creates an adjustment transaction (INCOME or EXPENSE) based on the difference.

## Prerequisites
- User must create two categories:
  - **"Reconciliation-Inc"** with type `INCOME`
  - **"Reconciliation-Exp"** with type `EXPENSE`

## Architecture

### Flow
1. User clicks "Reconcile Balance" on account show page
2. Dialog opens showing current tracked balance
3. User enters actual bank balance
4. System calculates: `difference = actual_balance - tracked_balance`
5. Based on difference:
   - **Positive**: Create INCOME transaction (bank has more than tracked)
   - **Negative**: Create EXPENSE transaction (bank has less than tracked)
   - **Zero**: No action needed
6. Transaction is created with "Reconciliation" category, account balance updated

---

## Files to Create

### 1. Action: `app/Actions/AccountReconcileAction.php`
```php
- Calculate difference between actual and tracked balance
- Query category by name: "Reconciliation-Inc" or "Reconciliation-Exp" based on difference sign
- Use DB::transaction() for atomicity
- Create transaction with auto-generated description
- Update account balance via increment/decrement
- Return the created transaction (or null if no difference)
```

### 2. Form Request: `app/Http/Requests/ReconcileAccountRequest.php`
```php
- Validate actual_balance: required, numeric, min:0
- Authorization: user owns the account
- Store Account in Context for controller
```

### 3. Controller: `app/Http/Controllers/ReconciliationController.php`
```php
- Invokable controller (single __invoke method)
- Injects ReconcileAccountRequest and AccountReconcileAction
- Returns back with success/info message
```

### 4. Frontend Form: `resources/js/forms/reconcile-balance-form.tsx`
```php
- useForm with actual_balance field
- Display current tracked balance (read-only)
- Show calculated difference with +/- indicator
- Submit to POST /account/{account}/reconcile
```

### 5. Frontend Dialog: `resources/js/components/reconcile-balance-dialog.tsx`
```php
- Wrapper around Dialog component
- Contains ReconcileBalanceForm
- Props: account, isOpen, onClose
- Close on successful submission
```

---

## Files to Modify

### 1. Routes: `routes/web.php`
Add route:
```php
Route::post('account/{account}/reconcile', ReconciliationController::class)
    ->name('account.reconcile');
```

### 2. Account Show Page: `resources/js/pages/accounts/show.tsx`
- Add state for dialog visibility
- Add "Reconcile Balance" button next to heading
- Include ReconcileBalanceDialog component

---

## Implementation Details

### AccountReconcileAction Logic
```php
public function execute(Account $account, float $actualBalance): ?Transaction
{
    $difference = $actualBalance - (float) $account->balance;

    if ($difference == 0) {
        return null;
    }

    $isIncome = $difference > 0;
    $categoryName = $isIncome ? 'Reconciliation-Inc' : 'Reconciliation-Exp';

    $category = Category::where('name', $categoryName)->firstOrFail();

    return DB::transaction(function () use ($account, $category, $difference, $isIncome) {
        $amount = abs($difference);

        $transaction = Transaction::create([
            'user_id' => $account->user_id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => $amount,
            'date' => now()->toDateString(),
            'description' => sprintf(
                'Balance reconciliation: %s%.2f',
                $isIncome ? '+' : '-',
                $amount
            ),
            'type' => TransactionSourceTypeEnum::NORMAL->value,
        ]);

        if ($isIncome) {
            $account->increment('balance', $amount);
        } else {
            $account->decrement('balance', $amount);
        }

        return $transaction;
    });
}
```

### Frontend Form UX
- Show current balance: "Current tracked balance: ₹10,000"
- Input field: "Actual bank balance"
- Dynamic difference display:
  - If actual > tracked: "Difference: +₹500 (will add income)"
  - If actual < tracked: "Difference: -₹500 (will add expense)"
  - If equal: "Balance matches - no adjustment needed"

---

## File Change Summary

| File | Action |
|------|--------|
| `app/Actions/AccountReconcileAction.php` | Create |
| `app/Http/Requests/ReconcileAccountRequest.php` | Create |
| `app/Http/Controllers/ReconciliationController.php` | Create |
| `routes/web.php` | Modify (add route) |
| `resources/js/forms/reconcile-balance-form.tsx` | Create |
| `resources/js/components/reconcile-balance-dialog.tsx` | Create |
| `resources/js/pages/accounts/show.tsx` | Modify (add dialog + button) |
| `tests/Feature/AccountReconcileTest.php` | Create |

---

## Verification

### Manual Testing
1. Create categories: "Reconciliation-Inc" (INCOME type) and "Reconciliation-Exp" (EXPENSE type)
2. Navigate to an account show page
3. Click "Reconcile Balance" button
4. Enter an actual balance higher than tracked → verify INCOME transaction created
5. Enter an actual balance lower than tracked → verify EXPENSE transaction created
6. Enter same balance → verify no transaction created
7. Verify account balance updates correctly after reconciliation

### Automated Tests
Run: `php artisan test --filter=AccountReconcile`

Test cases:
- Reconcile with positive difference creates INCOME transaction
- Reconcile with negative difference creates EXPENSE transaction
- Reconcile with zero difference creates no transaction
- Account balance is updated correctly
- Unauthorized user cannot reconcile another user's account
- Missing "Reconciliation-Inc" or "Reconciliation-Exp" category throws appropriate error
