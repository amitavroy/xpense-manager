<?php

use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 1000.00,
    ]);
});

test('reconcile with positive difference creates income transaction', function () {
    Category::factory()->create([
        'name' => 'Reconciliation-Inc',
        'type' => TransactionTypeEnum::INCOME,
    ]);
    Category::factory()->create([
        'name' => 'Reconciliation-Exp',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('account.reconcile', $this->account), [
        'actual_balance' => 1500.00,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->account->refresh();
    expect($this->account->balance)->toBe('1500.00');

    $transaction = Transaction::where('account_id', $this->account->id)
        ->whereHas('category', fn ($q) => $q->where('name', 'Reconciliation-Inc'))
        ->first();

    expect($transaction)->not->toBeNull()
        ->amount->toBe('500.00')
        ->description->toContain('Balance reconciliation')
        ->description->toContain('+');
});

test('reconcile with negative difference creates expense transaction', function () {
    Category::factory()->create([
        'name' => 'Reconciliation-Inc',
        'type' => TransactionTypeEnum::INCOME,
    ]);
    Category::factory()->create([
        'name' => 'Reconciliation-Exp',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('account.reconcile', $this->account), [
        'actual_balance' => 600.00,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->account->refresh();
    expect($this->account->balance)->toBe('600.00');

    $transaction = Transaction::where('account_id', $this->account->id)
        ->whereHas('category', fn ($q) => $q->where('name', 'Reconciliation-Exp'))
        ->first();

    expect($transaction)->not->toBeNull()
        ->amount->toBe('400.00')
        ->description->toContain('Balance reconciliation')
        ->description->toContain('-');
});

test('reconcile with zero difference creates no transaction', function () {
    Category::factory()->create([
        'name' => 'Reconciliation-Inc',
        'type' => TransactionTypeEnum::INCOME,
    ]);
    Category::factory()->create([
        'name' => 'Reconciliation-Exp',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);

    $this->actingAs($this->user);

    $initialTransactionCount = Transaction::where('account_id', $this->account->id)->count();

    $response = $this->post(route('account.reconcile', $this->account), [
        'actual_balance' => 1000.00,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->account->refresh();
    expect($this->account->balance)->toBe('1000.00');

    expect(Transaction::where('account_id', $this->account->id)->count())
        ->toBe($initialTransactionCount);
});

test('account balance is updated correctly after reconciliation', function () {
    Category::factory()->create([
        'name' => 'Reconciliation-Inc',
        'type' => TransactionTypeEnum::INCOME,
    ]);
    Category::factory()->create([
        'name' => 'Reconciliation-Exp',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);

    $this->actingAs($this->user);

    $this->post(route('account.reconcile', $this->account), [
        'actual_balance' => 1234.56,
    ]);

    $this->account->refresh();
    expect($this->account->balance)->toBe('1234.56');
});

test('unauthorized user cannot reconcile another users account', function () {
    $otherUser = User::factory()->create();
    Category::factory()->create([
        'name' => 'Reconciliation-Inc',
        'type' => TransactionTypeEnum::INCOME,
    ]);
    Category::factory()->create([
        'name' => 'Reconciliation-Exp',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);

    $this->actingAs($otherUser);

    $response = $this->post(route('account.reconcile', $this->account), [
        'actual_balance' => 1500.00,
    ]);

    $response->assertForbidden();

    $this->account->refresh();
    expect($this->account->balance)->toBe('1000.00');
});

test('missing reconciliation income category throws error', function () {
    Category::factory()->create([
        'name' => 'Reconciliation-Exp',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('account.reconcile', $this->account), [
        'actual_balance' => 1500.00,
    ]);

    $response->assertNotFound();
});

test('missing reconciliation expense category throws error', function () {
    Category::factory()->create([
        'name' => 'Reconciliation-Inc',
        'type' => TransactionTypeEnum::INCOME,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('account.reconcile', $this->account), [
        'actual_balance' => 500.00,
    ]);

    $response->assertNotFound();
});

test('actual_balance is required', function () {
    Category::factory()->create([
        'name' => 'Reconciliation-Inc',
        'type' => TransactionTypeEnum::INCOME,
    ]);
    Category::factory()->create([
        'name' => 'Reconciliation-Exp',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('account.reconcile', $this->account), []);

    $response->assertSessionHasErrors('actual_balance');
});

test('guest cannot reconcile account', function () {
    Category::factory()->create([
        'name' => 'Reconciliation-Inc',
        'type' => TransactionTypeEnum::INCOME,
    ]);
    Category::factory()->create([
        'name' => 'Reconciliation-Exp',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);

    $response = $this->post(route('account.reconcile', $this->account), [
        'actual_balance' => 1500.00,
    ]);

    $response->assertRedirect(route('login'));
});
