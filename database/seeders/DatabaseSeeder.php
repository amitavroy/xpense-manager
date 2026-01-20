<?php

namespace Database\Seeders;

use App\Enums\AccountTypeEnum;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\TransactionSourceTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Biller;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::firstOrCreate(
            ['email' => 'reachme@amitavroy.com'],
            [
                'name' => 'Amitav Roy',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
            ]
        );

        $this->setupDataForUser1($user);

        $user2 = User::firstOrCreate(
            ['email' => 'jhon.doe@gmail.com'],
            [
                'name' => 'Jhon Doe',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
            ]
        );

        collect([
            'HDFC Bank Saving',
        ])->each(function ($account) use ($user2) {
            Account::factory()->create([
                'user_id' => $user2->id,
                'name' => $account,
                'currency' => 'INR',
            ]);
        });
    }

    private function setupDataForUser1(User $user): void
    {
        collect([
            'HDFC Bank Saving',
            'ICICI Bank Savings',
            'Axis Bank Salary',
        ])->each(function ($account) use ($user) {
            Account::factory()->create([
                'user_id' => $user->id,
                'name' => $account,
                'currency' => 'INR',
            ]);
        });

        Account::factory()->create([
            'user_id' => $user->id,
            'name' => 'ICICI Bank Credit Card',
            'currency' => 'INR',
            'type' => AccountTypeEnum::CREDIT_CARD,
            'credit_limit' => 100000,
        ]);

        collect([
            'Food',
            'Transport',
            'Rent',
            'Bills',
            'Entertainment',
            'Other',
        ])->each(function ($category) {
            Category::factory()->create([
                'name' => $category,
                'type' => TransactionTypeEnum::EXPENSE,
            ]);
        });

        collect([
            'Salary',
            'Freelance',
            'Other',
        ])->each(function ($category) {
            Category::factory()->create([
                'name' => $category,
                'type' => TransactionTypeEnum::INCOME,
            ]);
        });

        collect([
            [
                'user_id' => $user->id,
                'account_id' => 1,
                'category_id' => 1,
                'amount' => 500,
                'description' => 'Dinner with friends',
                'date' => now(),
                'type' => TransactionSourceTypeEnum::NORMAL->value,
            ],
            [
                'user_id' => $user->id,
                'account_id' => 1,
                'category_id' => 1,
                'amount' => 250,
                'description' => 'Lunch with friends',
                'date' => now(),
                'type' => TransactionSourceTypeEnum::NORMAL->value,
            ],
            [
                'user_id' => $user->id,
                'account_id' => 1,
                'category_id' => 2,
                'amount' => 150,
                'description' => 'Going to office',
                'date' => now(),
                'type' => TransactionSourceTypeEnum::NORMAL->value,
            ],
        ])->each(function ($transaction) {
            Transaction::create($transaction);
        });

        collect([
            [
                'name' => 'Airtel',
                'description' => 'Airtel Monthly Bill',
                'category_id' => 4,
                'user_id' => $user->id,
            ],
            [
                'name' => 'Vodafone',
                'description' => 'Vodafone prepaid recharge',
                'category_id' => 4,
                'user_id' => $user->id,
            ],
            [
                'name' => 'Airtel Internet',
                'description' => 'Airtel Internet Bill',
                'category_id' => 4,
                'user_id' => $user->id,
            ],
        ])->each(function ($biller) {
            Biller::create($biller);
        });
    }
}
