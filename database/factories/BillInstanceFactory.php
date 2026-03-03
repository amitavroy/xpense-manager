<?php

namespace Database\Factories;

use App\Enums\BillStatusEnum;
use App\Models\Bill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillInstance>
 */
class BillInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'transaction_id' => null,
            'due_date' => $this->faker->date(),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'status' => BillStatusEnum::PENDING->value,
            'paid_date' => null,
            'notes' => null,
        ];
    }
}
