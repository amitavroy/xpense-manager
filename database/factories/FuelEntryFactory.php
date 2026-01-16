<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FuelEntry>
 */
class FuelEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'account_id' => \App\Models\Account::factory(),
            'date' => $this->faker->date(),
            'odometer_reading' => $this->faker->numberBetween(0, 999999),
            'fuel_quantity' => $this->faker->randomFloat(2, 1, 100),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'petrol_station_name' => $this->faker->company(),
        ];
    }
}
