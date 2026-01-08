<?php

use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\TransactionQuery;
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
