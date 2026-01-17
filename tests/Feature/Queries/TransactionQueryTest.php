<?php

use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\TransactionQuery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

test('recentTransactions returns a builder instance', function () {
    $query = new TransactionQuery;

    $result = $query->recentTransactions();

    expect($result)->toBeInstanceOf(Builder::class);
});

test('recentTransactions eager loads account relationship', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    $query = new TransactionQuery;
    $transactions = $query->recentTransactions()->get();

    expect($transactions)->not->toBeEmpty();
    expect($transactions->first()->relationLoaded('account'))->toBeTrue();
    expect($transactions->first()->account)->toBeInstanceOf(Account::class);
});

test('recentTransactions eager loads category relationship', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    $query = new TransactionQuery;
    $transactions = $query->recentTransactions()->get();

    expect($transactions)->not->toBeEmpty();
    expect($transactions->first()->relationLoaded('category'))->toBeTrue();
    expect($transactions->first()->category)->toBeInstanceOf(Category::class);
});

test('recentTransactions eager loads user relationship', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    $query = new TransactionQuery;
    $transactions = $query->recentTransactions()->get();

    expect($transactions)->not->toBeEmpty();
    expect($transactions->first()->relationLoaded('user'))->toBeTrue();
    expect($transactions->first()->user)->toBeInstanceOf(User::class);
});

test('recentTransactions only loads user name and email', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    $query = new TransactionQuery;
    $transactions = $query->recentTransactions()->get();
    $loadedUser = $transactions->first()->user;

    expect($loadedUser->id)->toBe($user->id);
    expect($loadedUser->name)->toBe('John Doe');
    expect($loadedUser->email)->toBe('john@example.com');
    expect($loadedUser->getAttributes())->toHaveKeys(['id', 'name', 'email']);
    expect($loadedUser->getAttributes())->not->toHaveKey('password');
    expect($loadedUser->getAttributes())->not->toHaveKey('email_verified_at');
});

test('recentTransactions orders by date descending', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();

    $olderTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'date' => '2024-01-01',
    ]);

    $newerTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'date' => '2024-01-15',
    ]);

    $query = new TransactionQuery;
    $transactions = $query->recentTransactions()->get();

    expect($transactions->first()->id)->toBe($newerTransaction->id);
    expect($transactions->last()->id)->toBe($olderTransaction->id);
});

test('recentTransactions orders by id descending when dates are the same', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();

    $firstTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'date' => '2024-01-15',
    ]);

    $secondTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'date' => '2024-01-15',
    ]);

    $query = new TransactionQuery;
    $transactions = $query->recentTransactions()->get();

    expect($transactions->first()->id)->toBe($secondTransaction->id);
    expect($transactions->skip(1)->first()->id)->toBe($firstTransaction->id);
});

test('recentTransactions returns all transactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();

    Transaction::factory()->count(5)->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    $query = new TransactionQuery;
    $transactions = $query->recentTransactions()->get();

    expect($transactions)->toHaveCount(5);
});

test('recentTransactions can be paginated', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();

    Transaction::factory()->count(15)->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    $query = new TransactionQuery;
    $paginated = $query->recentTransactions()->paginate(10);

    expect($paginated->items())->toHaveCount(10);
    expect($paginated->total())->toBe(15);
    expect($paginated->hasMorePages())->toBeTrue();
});

test('whereCategoryType filters transactions by expense type', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
    ]);

    $filtered = Transaction::query()
        ->whereCategoryType(TransactionTypeEnum::EXPENSE)
        ->get();

    expect($filtered)->toHaveCount(1);
    expect($filtered->first()->category_id)->toBe($expenseCategory->id);
    expect($filtered->first()->category->type)->toBe(TransactionTypeEnum::EXPENSE);
});

test('whereCategoryType filters transactions by income type', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
    ]);

    $filtered = Transaction::query()
        ->whereCategoryType(TransactionTypeEnum::INCOME)
        ->get();

    expect($filtered)->toHaveCount(1);
    expect($filtered->first()->category_id)->toBe($incomeCategory->id);
    expect($filtered->first()->category->type)->toBe(TransactionTypeEnum::INCOME);
});

test('whereCategoryType can be chained with recentTransactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'date' => '2024-01-20',
    ]);

    $query = new TransactionQuery;
    $filtered = $query->recentTransactions()
        ->whereCategoryType(TransactionTypeEnum::EXPENSE)
        ->get();

    expect($filtered)->toHaveCount(1);
    expect($filtered->first()->category_id)->toBe($expenseCategory->id);
    expect($filtered->first()->category->type)->toBe(TransactionTypeEnum::EXPENSE);
    expect($filtered->first()->relationLoaded('account'))->toBeTrue();
    expect($filtered->first()->relationLoaded('category'))->toBeTrue();
});

test('getTotalExpenseForMonth returns correct total for a specific month', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100.50,
        'date' => '2024-01-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 200.75,
        'date' => '2024-01-20',
    ]);

    $query = new TransactionQuery;
    $reflection = new ReflectionClass($query);
    $method = $reflection->getMethod('getTotalExpenseForMonth');
    $method->setAccessible(true);

    $result = $method->invoke($query, Carbon::parse('2024-01-15'));

    expect($result)->toBe(301.25);
});

test('getTotalExpenseForMonth only includes expense transactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100.00,
        'date' => '2024-01-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'amount' => 500.00,
        'date' => '2024-01-15',
    ]);

    $query = new TransactionQuery;
    $reflection = new ReflectionClass($query);
    $method = $reflection->getMethod('getTotalExpenseForMonth');
    $method->setAccessible(true);

    $result = $method->invoke($query, Carbon::parse('2024-01-15'));

    expect($result)->toBe(100.00);
});

test('getTotalExpenseForMonth correctly filters by date range', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100.00,
        'date' => '2024-01-01',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 200.00,
        'date' => '2024-01-31',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 300.00,
        'date' => '2024-02-01',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 400.00,
        'date' => '2023-12-31',
    ]);

    $query = new TransactionQuery;
    $reflection = new ReflectionClass($query);
    $method = $reflection->getMethod('getTotalExpenseForMonth');
    $method->setAccessible(true);

    $result = $method->invoke($query, Carbon::parse('2024-01-15'));

    expect($result)->toBe(300.00);
});

test('getTotalExpenseForMonth returns zero when there are no expenses', function () {
    $query = new TransactionQuery;
    $reflection = new ReflectionClass($query);
    $method = $reflection->getMethod('getTotalExpenseForMonth');
    $method->setAccessible(true);

    $result = $method->invoke($query, Carbon::parse('2024-01-15'));

    expect($result)->toBe(0.0);
});

test('getTotalExpenseForMonth works with Carbon instance parameter', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 150.00,
        'date' => '2024-03-15',
    ]);

    $query = new TransactionQuery;
    $reflection = new ReflectionClass($query);
    $method = $reflection->getMethod('getTotalExpenseForMonth');
    $method->setAccessible(true);

    $result = $method->invoke($query, Carbon::parse('2024-03-15'));

    expect($result)->toBe(150.00);
});

test('getTotalExpenseForMonth works with string parameter', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 250.00,
        'date' => '2024-04-15',
    ]);

    $query = new TransactionQuery;
    $reflection = new ReflectionClass($query);
    $method = $reflection->getMethod('getTotalExpenseForMonth');
    $method->setAccessible(true);

    $result = $method->invoke($query, '2024-04-15');

    expect($result)->toBe(250.00);
});

test('getTotalExpenseForMonth handles transactions on first and last day of month', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 50.00,
        'date' => '2024-05-01',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 75.00,
        'date' => '2024-05-31',
    ]);

    $query = new TransactionQuery;
    $reflection = new ReflectionClass($query);
    $method = $reflection->getMethod('getTotalExpenseForMonth');
    $method->setAccessible(true);

    $result = $method->invoke($query, Carbon::parse('2024-05-15'));

    expect($result)->toBe(125.00);
});

test('expenseStats returns current and previous month totals', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $currentMonth = Carbon::now();
    $previousMonth = Carbon::now()->subMonth();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100.00,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 200.00,
        'date' => $previousMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    $query = new TransactionQuery;
    $result = $query->expenseStats();

    expect($result)->toHaveKey('currentMonthTotalExpense');
    expect($result)->toHaveKey('previousMonthTotalExpense');
    expect($result['currentMonthTotalExpense'])->toBe(100.00);
    expect($result['previousMonthTotalExpense'])->toBe(200.00);
});

test('incomes returns a builder instance', function () {
    $query = new TransactionQuery;

    $result = $query->incomes();

    expect($result)->toBeInstanceOf(Builder::class);
});

test('incomes eager loads account relationship', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
    ]);

    $query = new TransactionQuery;
    $incomes = $query->incomes()->get();

    expect($incomes)->not->toBeEmpty();
    expect($incomes->first()->relationLoaded('account'))->toBeTrue();
    expect($incomes->first()->account)->toBeInstanceOf(Account::class);
});

test('incomes eager loads category relationship', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
    ]);

    $query = new TransactionQuery;
    $incomes = $query->incomes()->get();

    expect($incomes)->not->toBeEmpty();
    expect($incomes->first()->relationLoaded('category'))->toBeTrue();
    expect($incomes->first()->category)->toBeInstanceOf(Category::class);
});

test('incomes filters only income transactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
    ]);

    $query = new TransactionQuery;
    $incomes = $query->incomes()->get();

    expect($incomes)->toHaveCount(1);
    expect($incomes->first()->category_id)->toBe($incomeCategory->id);
    expect($incomes->first()->category->type)->toBe(TransactionTypeEnum::INCOME);
});

test('incomes orders by date descending', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    $olderIncome = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'date' => '2024-01-01',
    ]);

    $newerIncome = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'date' => '2024-01-15',
    ]);

    $query = new TransactionQuery;
    $incomes = $query->incomes()->get();

    expect($incomes->first()->id)->toBe($newerIncome->id);
    expect($incomes->last()->id)->toBe($olderIncome->id);
});

test('incomes orders by id descending when dates are the same', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    $firstIncome = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'date' => '2024-01-15',
    ]);

    $secondIncome = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'date' => '2024-01-15',
    ]);

    $query = new TransactionQuery;
    $incomes = $query->incomes()->get();

    expect($incomes->first()->id)->toBe($secondIncome->id);
    expect($incomes->skip(1)->first()->id)->toBe($firstIncome->id);
});

test('incomes can be paginated', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    Transaction::factory()->count(15)->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
    ]);

    $query = new TransactionQuery;
    $paginated = $query->incomes()->paginate(10);

    expect($paginated->items())->toHaveCount(10);
    expect($paginated->total())->toBe(15);
    expect($paginated->hasMorePages())->toBeTrue();
});

test('expenses returns a builder instance', function () {
    $query = new TransactionQuery;

    $result = $query->expenses();

    expect($result)->toBeInstanceOf(Builder::class);
});

test('expenses filters by expense type only', function () {
    $user = User::factory()->create();
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

    $query = new TransactionQuery;
    $expenses = $query->expenses()->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->category->type)->toBe(TransactionTypeEnum::EXPENSE);
});

test('expenses filters by user_id when provided', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
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

    $query = new TransactionQuery;
    $expenses = $query->expenses(userId: $user1->id)->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->user_id)->toBe($user1->id);
});

test('expenses defaults to current month when no dates provided', function () {
    $user = User::factory()->create();
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

    $query = new TransactionQuery;
    $expenses = $query->expenses()->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m'))->toBe($currentMonth->format('Y-m'));
});

test('expenses filters by from_date', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-05',
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses(fromDate: '2024-01-10')->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m-d'))->toBe('2024-01-15');
});

test('expenses filters by to_date', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

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
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(25)->format('Y-m-d'),
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses(toDate: $currentMonth->copy()->day(20)->format('Y-m-d'))->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m-d'))->toBe($currentMonth->copy()->day(15)->format('Y-m-d'));
});

test('expenses filters by date range', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-05',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-25',
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses(fromDate: '2024-01-10', toDate: '2024-01-20')->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m-d'))->toBe('2024-01-15');
});

test('expenses includes boundary dates', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-10',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => '2024-01-20',
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses(fromDate: '2024-01-10', toDate: '2024-01-20')->get();

    expect($expenses)->toHaveCount(2);
});

test('expenses preset last_30_days filters correctly', function () {
    $user = User::factory()->create();
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

    $query = new TransactionQuery;
    $expenses = $query->expenses(preset: 'last_30_days')->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m-d'))->toBe($now->copy()->subDays(15)->format('Y-m-d'));
});

test('expenses preset this_month filters correctly', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $now = Carbon::now();
    $previousMonth = $now->copy()->subMonth();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $now->copy()->day(15)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $previousMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses(preset: 'this_month')->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m'))->toBe($now->format('Y-m'));
});

test('expenses preset last_month filters correctly', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $now = Carbon::now();
    $previousMonth = $now->copy()->subMonth();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $previousMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $now->copy()->day(15)->format('Y-m-d'),
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses(preset: 'last_month')->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m'))->toBe($previousMonth->format('Y-m'));
});

test('expenses preset last_week filters correctly', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $now = Carbon::now();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $now->copy()->subDays(3)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $now->copy()->subDays(10)->format('Y-m-d'),
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses(preset: 'last_week')->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m-d'))->toBe($now->copy()->subDays(3)->format('Y-m-d'));
});

test('expenses preset overrides manual dates', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $now = Carbon::now();
    $previousMonth = $now->copy()->subMonth();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $now->copy()->day(15)->format('Y-m-d'),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $previousMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses(
        fromDate: $previousMonth->copy()->startOfMonth()->format('Y-m-d'),
        toDate: $previousMonth->copy()->endOfMonth()->format('Y-m-d'),
        preset: 'this_month'
    )->get();

    expect($expenses)->toHaveCount(1);
    expect($expenses->first()->date->format('Y-m'))->toBe($now->format('Y-m'));
});

test('expenses eager loads relationships', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $currentMonth = Carbon::now();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses()->get();

    expect($expenses)->not->toBeEmpty();
    expect($expenses->first()->relationLoaded('account'))->toBeTrue();
    expect($expenses->first()->relationLoaded('category'))->toBeTrue();
    expect($expenses->first()->relationLoaded('user'))->toBeTrue();
});

test('expenses orders by date descending then id descending', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    $currentMonth = Carbon::now();

    $olderTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(15)->format('Y-m-d'),
    ]);

    $newerTransaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'date' => $currentMonth->copy()->day(20)->format('Y-m-d'),
    ]);

    $query = new TransactionQuery;
    $expenses = $query->expenses()->get();

    expect($expenses->first()->id)->toBe($newerTransaction->id);
    expect($expenses->last()->id)->toBe($olderTransaction->id);
});
