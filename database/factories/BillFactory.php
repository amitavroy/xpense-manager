<?php

namespace Database\Factories;

use App\Enums\BillFrequencyEnum;
use App\Models\Biller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
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
            'biller_id' => Biller::factory(),
            'default_amount' => $this->faker->randomFloat(2, 100, 5000),
            'frequency' => BillFrequencyEnum::MONTHLY->value,
            'interval_days' => null,
            'next_payment_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'is_active' => true,
            'auto_generate_bill' => true,
        ];
    }
}
