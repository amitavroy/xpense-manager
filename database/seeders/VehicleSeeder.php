<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if ($user) {
            collect([
                [
                    'name' => 'Honda City',
                    'company_name' => 'Honda',
                    'registration_number' => 'MH12AB1234',
                    'kilometers' => 50000,
                ],
                [
                    'name' => 'Toyota Camry',
                    'company_name' => 'Toyota',
                    'registration_number' => 'DL8CD5678',
                    'kilometers' => 25000,
                ],
                [
                    'name' => 'Maruti Swift',
                    'company_name' => 'Maruti Suzuki',
                    'registration_number' => 'KA01EF9012',
                    'kilometers' => 0,
                ],
            ])->each(function ($vehicle) use ($user) {
                Vehicle::factory()->create([
                    'user_id' => $user->id,
                    'name' => $vehicle['name'],
                    'company_name' => $vehicle['company_name'],
                    'registration_number' => $vehicle['registration_number'],
                    'kilometers' => $vehicle['kilometers'],
                ]);
            });
        }
    }
}
