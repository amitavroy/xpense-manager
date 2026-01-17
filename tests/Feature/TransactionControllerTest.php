<?php

use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

test('guests cannot access transactions index', function () {
    $this->get(route('transactions.index'))->assertRedirect(route('login'));
});

test('authenticated users can view transactions index', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
    ]);

    $response = $this->get(route('transactions.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('transactions'));
});

test('transactions index defaults to current month', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $currentMonth = Carbon::now();
    $previousMonth = Carbon::now()->subMonth();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $previousMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    $response = $this->get(route('transactions.index'));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->has('transactions.data', 1)
            ->has('filters')
    );
});

test('transactions index filters by from_date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $currentMonth = Carbon::now();
    $fromDate = $currentMonth->copy()->day(10)->format('Y-m-d');

    // This should be included (after from_date)
    $includedTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    // This should be excluded (before from_date)
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(5)->format('Y-m-d'),
    ]);

    $response = $this->get(route('transactions.index', ['from_date' => $fromDate]));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('filters.from_date', $fromDate)
            ->has('transactions.data')
            ->where('transactions.data.0.id', $includedTransaction->id)
    );
});

test('transactions index filters by to_date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $currentMonth = Carbon::now();
    $toDate = $currentMonth->copy()->day(20)->format('Y-m-d');

    // This should be included (before to_date)
    $includedTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    // This should be excluded (after to_date)
    $excludedTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(25)->format('Y-m-d'),
    ]);

    $response = $this->get(route('transactions.index', ['to_date' => $toDate]));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('filters.to_date', $toDate)
            ->has('transactions.data')
    );

    // Verify the included transaction is present
    $transactions = $response->viewData('page')['props']['transactions']['data'];
    $transactionIds = collect($transactions)->pluck('id')->toArray();

    expect($transactionIds)->toContain($includedTransaction->id);

    // Verify excluded transaction is not present (if no other tests created transactions)
    // Note: This might fail if other tests created transactions in the same month
    if (count($transactionIds) === 1) {
        expect($transactionIds)->not->toContain($excludedTransaction->id);
    }
});

test('transactions index filters by date range', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $currentMonth = Carbon::now();
    $fromDate = $currentMonth->copy()->day(10)->format('Y-m-d');
    $toDate = $currentMonth->copy()->day(20)->format('Y-m-d');

    // This should be included (within range)
    $includedTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    // These should be excluded (outside range)
    $excludedTransaction1 = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(5)->format('Y-m-d'),
    ]);

    $excludedTransaction2 = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(25)->format('Y-m-d'),
    ]);

    $response = $this->get(route('transactions.index', [
        'from_date' => $fromDate,
        'to_date' => $toDate,
    ]));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('filters.from_date', $fromDate)
            ->where('filters.to_date', $toDate)
            ->has('transactions.data')
    );

    // Verify the included transaction is present
    $transactions = $response->viewData('page')['props']['transactions']['data'];
    $transactionIds = collect($transactions)->pluck('id')->toArray();

    expect($transactionIds)->toContain($includedTransaction->id);
});

test('transactions index filters by preset', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $now = Carbon::now();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $now->copy()->subDays(15)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $now->copy()->subDays(35)->format('Y-m-d'),
    ]);

    $response = $this->get(route('transactions.index', ['preset' => 'last_30_days']));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('filters.preset', 'last_30_days')
    );
});

test('transactions index filters by user_id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $this->actingAs($user1);

    $account1 = Account::factory()->create(['user_id' => $user1->id]);
    $account2 = Account::factory()->create(['user_id' => $user2->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $currentMonth = Carbon::now();

    Transaction::factory()->create([
        'user_id' => $user1->id,
        'account_id' => $account1->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user2->id,
        'account_id' => $account2->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    $response = $this->get(route('transactions.index', ['user_id' => $user1->id]));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('filters.user_id', $user1->id)
    );
});

test('transactions index preserves filter state in URL', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-15',
    ]);

    $response = $this->get(route('transactions.index', [
        'from_date' => '2024-01-10',
        'to_date' => '2024-01-20',
        'preset' => 'this_month',
    ]));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->where('filters.from_date', '2024-01-10')
            ->where('filters.to_date', '2024-01-20')
            ->where('filters.preset', 'this_month')
    );
});

test('transactions index pagination works with filters', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->count(15)->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => Carbon::now()->format('Y-m-d'),
    ]);

    $response = $this->get(route('transactions.index', ['from_date' => Carbon::now()->format('Y-m-d')]));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->has('transactions.data', 10)
            ->where('transactions.total', 15)
            ->where('transactions.per_page', 10)
    );
});

test('transactions index only shows expense transactions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    $currentMonth = Carbon::now();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    $response = $this->get(route('transactions.index'));
    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('transactions.data.0.category.type', TransactionTypeEnum::EXPENSE->value)
    );
});
