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
