<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripExpense>
 */
class TripExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'paid_by' => User::factory(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence(),
            'is_shared' => true,
        ];
    }
}
