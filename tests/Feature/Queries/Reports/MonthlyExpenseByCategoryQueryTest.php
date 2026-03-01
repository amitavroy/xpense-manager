<?php

use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Queries\Reports\MonthlyExpenseByCategoryQuery;
use Illuminate\Support\Collection;

test('execute returns collection with month key and one category total', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100.00,
        'date' => '2025-01-15',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $query = new MonthlyExpenseByCategoryQuery;
    $result = $query->execute('2025-01-01', '2025-01-31', $user->id);

    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(1);
    expect($result->first()['month'])->toBe('2025-01');
    expect($result->first()['categories'])->toHaveCount(1);
    expect($result->first()['categories'][0])->toMatchArray([
        'category_id' => $expenseCategory->id,
        'category_name' => $expenseCategory->name,
        'total' => 100.0,
    ]);
});

test('execute returns multiple categories in same month with correct totals', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $food = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE, 'name' => 'Food']);
    $transport = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE, 'name' => 'Transport']);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $food->id,
        'amount' => 50.00,
        'date' => '2025-02-10',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $transport->id,
        'amount' => 75.50,
        'date' => '2025-02-20',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);

    $query = new MonthlyExpenseByCategoryQuery;
    $result = $query->execute('2025-02-01', '2025-02-28', $user->id);

    expect($result)->toHaveCount(1);
    expect($result->first()['categories'])->toHaveCount(2);
    $categories = collect($result->first()['categories'])->keyBy('category_id');
    expect($categories[$food->id])->toMatchArray(['category_name' => 'Food', 'total' => 50.0]);
    expect($categories[$transport->id])->toMatchArray(['category_name' => 'Transport', 'total' => 75.5]);
});

test('execute returns all months in range with empty categories when no transactions', function () {
    $user = User::factory()->create();

    $query = new MonthlyExpenseByCategoryQuery;
    $result = $query->execute('2025-01-15', '2025-03-20', $user->id);

    expect($result)->toHaveCount(3);
    expect($result->pluck('month')->all())->toBe(['2025-01', '2025-02', '2025-03']);
    $result->each(function ($row) {
        expect($row['categories'])->toBeArray();
        expect($row['categories'])->toHaveCount(0);
    });
});

test('execute excludes reconciliation transactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100.00,
        'date' => '2025-03-15',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 999.00,
        'date' => '2025-03-15',
        'type' => TransactionSourceTypeEnum::RECONCILIATION,
    ]);

    $query = new MonthlyExpenseByCategoryQuery;
    $result = $query->execute('2025-03-01', '2025-03-31', $user->id);

    expect($result->first()['categories'])->toHaveCount(1);
    expect($result->first()['categories'][0]['total'])->toBe(100.0);
});

test('execute includes only expense category transactions', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);
    $incomeCategory = Category::factory()->create(['type' => TransactionTypeEnum::INCOME]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 200.00,
        'date' => '2025-04-15',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'amount' => 500.00,
        'date' => '2025-04-15',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $query = new MonthlyExpenseByCategoryQuery;
    $result = $query->execute('2025-04-01', '2025-04-30', $user->id);

    expect($result->first()['categories'])->toHaveCount(1);
    expect($result->first()['categories'][0]['category_id'])->toBe($expenseCategory->id);
    expect($result->first()['categories'][0]['total'])->toBe(200.0);
});

test('execute returns only data for the given user id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account1 = Account::factory()->create(['user_id' => $user1->id]);
    $account2 = Account::factory()->create(['user_id' => $user2->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user1->id,
        'account_id' => $account1->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100.00,
        'date' => '2025-05-15',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);
    Transaction::factory()->create([
        'user_id' => $user2->id,
        'account_id' => $account2->id,
        'category_id' => $expenseCategory->id,
        'amount' => 999.00,
        'date' => '2025-05-15',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $query = new MonthlyExpenseByCategoryQuery;
    $result = $query->execute('2025-05-01', '2025-05-31', $user1->id);

    expect($result->first()['categories'])->toHaveCount(1);
    expect($result->first()['categories'][0]['total'])->toBe(100.0);
});

test('execute uses start of month and end of month from date strings', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $expenseCategory = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 10.00,
        'date' => '2025-06-01',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'amount' => 20.00,
        'date' => '2025-06-30',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $query = new MonthlyExpenseByCategoryQuery;
    $result = $query->execute('2025-06-15', '2025-06-20', $user->id);

    expect($result->first()['categories'][0]['total'])->toBe(30.0);
    expect($result->first()['month'])->toBe('2025-06');
});

test('execute spans multiple months with correct totals per month', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);
    $food = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE, 'name' => 'Food']);
    $transport = Category::factory()->create(['type' => TransactionTypeEnum::EXPENSE, 'name' => 'Transport']);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $food->id,
        'amount' => 100.00,
        'date' => '2025-07-05',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $transport->id,
        'amount' => 50.00,
        'date' => '2025-08-15',
        'type' => TransactionSourceTypeEnum::CREDIT_CARD,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $food->id,
        'amount' => 25.00,
        'date' => '2025-09-25',
        'type' => TransactionSourceTypeEnum::NORMAL,
    ]);

    $query = new MonthlyExpenseByCategoryQuery;
    $result = $query->execute('2025-07-01', '2025-09-30', $user->id);

    expect($result)->toHaveCount(3);

    $july = $result->firstWhere('month', '2025-07');
    expect($july['categories'])->toHaveCount(1);
    expect(collect($july['categories'])->firstWhere('category_id', $food->id)['total'])->toBe(100.0);

    $august = $result->firstWhere('month', '2025-08');
    expect($august['categories'])->toHaveCount(1);
    expect(collect($august['categories'])->firstWhere('category_id', $transport->id)['total'])->toBe(50.0);

    $september = $result->firstWhere('month', '2025-09');
    expect($september['categories'])->toHaveCount(1);
    expect(collect($september['categories'])->firstWhere('category_id', $food->id)['total'])->toBe(25.0);
});
