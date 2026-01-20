<?php

namespace Database\Factories;

use App\Enums\TransactionSourceTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 0, 1000),
            'date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'type' => TransactionSourceTypeEnum::NORMAL->value,
        ];
    }
}
