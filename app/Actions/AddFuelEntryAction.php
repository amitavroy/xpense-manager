<?php

namespace App\Actions;

use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\FuelEntry;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class AddFuelEntryAction
{
    public function __construct(
        private readonly AddTransactionAction $addTransactionAction
    ) {}

    public function execute(array $data, User $user): FuelEntry
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;

            // Find the "Fuel" category - must exist
            $fuelCategory = Category::where('name', 'Fuel')
                ->where('type', TransactionTypeEnum::EXPENSE)
                ->firstOrFail();

            // Load the account
            $account = Account::findOrFail($data['account_id']);

            // Load the vehicle
            $vehicle = Vehicle::findOrFail($data['vehicle_id']);

            $previousEntry = FuelEntry::where('vehicle_id', $data['vehicle_id'])
                ->where('user_id', $user->id)
                ->orderBy('odometer_reading', 'desc')
                ->first();

            $metrics = FuelEntry::computeMetrics(
                $previousEntry,
                $data['odometer_reading'],
                $data['fuel_quantity'],
            );

            $fuelEntry = new FuelEntry($data);
            $fuelEntry->forceFill($metrics);
            $fuelEntry->save();

            // Update vehicle kilometers with the new odometer reading
            $vehicle->update([
                'kilometers' => $data['odometer_reading'],
            ]);

            // Create the transaction
            $this->addTransactionAction->execute(
                data: [
                    'amount' => $data['amount'],
                    'date' => $data['date'],
                    'description' => "Fuel for {$vehicle->name}",
                ],
                category: $fuelCategory,
                account: $account,
                user: $user
            );

            return $fuelEntry;
        });
    }
}
