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

            // Create the fuel entry
            $fuelEntry = FuelEntry::create($data);

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
