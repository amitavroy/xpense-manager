<?php

use App\Actions\AddFuelEntryAction;
use App\Actions\AddTransactionAction;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\FuelEntry;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->action = new AddFuelEntryAction(new AddTransactionAction);
    $this->user = User::factory()->create();
    $this->account = Account::factory()->create(['user_id' => $this->user->id, 'balance' => 1000.00]);
    $this->vehicle = Vehicle::factory()->create(['user_id' => $this->user->id]);
    $this->fuelCategory = Category::factory()->create([
        'name' => 'Fuel',
        'type' => TransactionTypeEnum::EXPENSE,
    ]);
});

test('can execute add fuel entry action and create fuel entry in database', function () {
    $data = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 3500.00,
        'petrol_station_name' => 'Shell Station',
    ];

    $fuelEntry = $this->action->execute($data, $this->user);

    expect($fuelEntry)
        ->toBeInstanceOf(FuelEntry::class)
        ->user_id->toBe($this->user->id)
        ->vehicle_id->toBe($this->vehicle->id)
        ->account_id->toBe($this->account->id)
        ->date->format('Y-m-d')->toBe('2024-01-15')
        ->odometer_reading->toBe(50000)
        ->fuel_quantity->toBe('45.50')
        ->amount->toBe('3500.00')
        ->petrol_station_name->toBe('Shell Station');

    // Verify fuel entry is persisted to database
    expect($fuelEntry->exists)->toBeTrue();
    expect($fuelEntry->id)->not->toBeNull();

    $retrievedFuelEntry = FuelEntry::find($fuelEntry->id);
    expect($retrievedFuelEntry)
        ->toBeInstanceOf(FuelEntry::class)
        ->user_id->toBe($this->user->id)
        ->vehicle_id->toBe($this->vehicle->id)
        ->account_id->toBe($this->account->id)
        ->amount->toBe('3500.00')
        ->petrol_station_name->toBe('Shell Station');
});

test('can execute add fuel entry action and create expense transaction', function () {
    $data = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 3500.00,
        'petrol_station_name' => 'Shell Station',
    ];

    $initialBalance = $this->account->balance;
    $fuelEntry = $this->action->execute($data, $this->user);

    // Verify transaction was created
    $transaction = Transaction::where('user_id', $this->user->id)
        ->where('account_id', $this->account->id)
        ->where('category_id', $this->fuelCategory->id)
        ->where('description', "Fuel for {$this->vehicle->name}")
        ->first();

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->user_id->toBe($this->user->id)
        ->account_id->toBe($this->account->id)
        ->category_id->toBe($this->fuelCategory->id)
        ->amount->toBe('3500.00')
        ->date->format('Y-m-d')->toBe('2024-01-15')
        ->description->toBe("Fuel for {$this->vehicle->name}");

    // Verify account balance was decremented
    $this->account->refresh();
    expect($this->account->balance)->toBe(number_format($initialBalance - $data['amount'], 2, '.', ''));
});

test('transaction description is correctly formatted with vehicle name', function () {
    $data = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 2500.00,
        'petrol_station_name' => 'BP Petrol Pump',
    ];

    $this->action->execute($data, $this->user);

    $transaction = Transaction::where('user_id', $this->user->id)
        ->where('description', "Fuel for {$this->vehicle->name}")
        ->first();

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->description->toBe("Fuel for {$this->vehicle->name}");
});

test('fuel entry and transaction are created in same database transaction', function () {
    $data = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 3500.00,
        'petrol_station_name' => 'Shell Station',
    ];

    $fuelEntry = $this->action->execute($data, $this->user);

    // Both should exist
    expect($fuelEntry->exists)->toBeTrue();
    $transaction = Transaction::where('user_id', $this->user->id)
        ->where('account_id', $this->account->id)
        ->where('category_id', $this->fuelCategory->id)
        ->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->exists)->toBeTrue();
});

test('fails when fuel category does not exist', function () {
    // Delete the fuel category
    Category::where('name', 'Fuel')->delete();

    $data = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 3500.00,
        'petrol_station_name' => 'Shell Station',
    ];

    expect(fn () => $this->action->execute($data, $this->user))
        ->toThrow(ModelNotFoundException::class);

    // Verify no fuel entry was created
    expect(FuelEntry::where('user_id', $this->user->id)->count())->toBe(0);

    // Verify no transaction was created
    expect(Transaction::where('user_id', $this->user->id)->count())->toBe(0);

    // Verify account balance was not changed
    $this->account->refresh();
    expect($this->account->balance)->toBe('1000.00');
});

test('can execute add fuel entry action with different amounts', function () {
    $testCases = [
        ['amount' => 100.50, 'petrol_station_name' => 'Station 1'],
        ['amount' => 2500.75, 'petrol_station_name' => 'Station 2'],
        ['amount' => 5000.00, 'petrol_station_name' => 'Station 3'],
    ];

    foreach ($testCases as $testCase) {
        $data = [
            'vehicle_id' => $this->vehicle->id,
            'account_id' => $this->account->id,
            'date' => '2024-01-15',
            'odometer_reading' => 50000,
            'fuel_quantity' => 45.50,
            'amount' => $testCase['amount'],
            'petrol_station_name' => $testCase['petrol_station_name'],
        ];

        $fuelEntry = $this->action->execute($data, $this->user);

        expect($fuelEntry->amount)->toBe(number_format($testCase['amount'], 2, '.', ''));

        $transaction = Transaction::where('user_id', $this->user->id)
            ->where('description', "Fuel for {$this->vehicle->name}")
            ->latest()
            ->first();

        expect($transaction)->not->toBeNull();
    }
});

test('can execute add fuel entry action with different dates', function () {
    $dates = ['2024-01-15', '2024-02-20', '2024-12-31'];

    foreach ($dates as $date) {
        $data = [
            'vehicle_id' => $this->vehicle->id,
            'account_id' => $this->account->id,
            'date' => $date,
            'odometer_reading' => 50000,
            'fuel_quantity' => 45.50,
            'amount' => 2000.00,
            'petrol_station_name' => 'Test Station',
        ];

        $fuelEntry = $this->action->execute($data, $this->user);

        expect($fuelEntry->date->format('Y-m-d'))->toBe($date);

        $transaction = Transaction::where('user_id', $this->user->id)
            ->where('description', "Fuel for {$this->vehicle->name}")
            ->whereDate('date', $date)
            ->latest()
            ->first();

        expect($transaction)->not->toBeNull();
        expect($transaction->date->format('Y-m-d'))->toBe($date);
    }
});

test('can execute add fuel entry action multiple times', function () {
    $data1 = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 2000.00,
        'petrol_station_name' => 'Station 1',
    ];

    $data2 = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-20',
        'odometer_reading' => 50500,
        'fuel_quantity' => 50.00,
        'amount' => 3500.00,
        'petrol_station_name' => 'Station 2',
    ];

    $fuelEntry1 = $this->action->execute($data1, $this->user);
    $fuelEntry2 = $this->action->execute($data2, $this->user);

    expect($fuelEntry1->id)->not->toBe($fuelEntry2->id);

    // Verify both transactions were created
    $transactions = Transaction::where('user_id', $this->user->id)
        ->where('category_id', $this->fuelCategory->id)
        ->get();

    expect($transactions->count())->toBe(2);

    // Verify account balance was decremented twice
    $this->account->refresh();
    expect($this->account->balance)->toBe(number_format(1000 - 2000 - 3500, 2, '.', ''));
});

test('fuel entry action works with different vehicles', function () {
    $vehicle2 = Vehicle::factory()->create(['user_id' => $this->user->id]);

    $data1 = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 2000.00,
        'petrol_station_name' => 'Station 1',
    ];

    $data2 = [
        'vehicle_id' => $vehicle2->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 30000,
        'fuel_quantity' => 40.00,
        'amount' => 2500.00,
        'petrol_station_name' => 'Station 2',
    ];

    $fuelEntry1 = $this->action->execute($data1, $this->user);
    $fuelEntry2 = $this->action->execute($data2, $this->user);

    expect($fuelEntry1->vehicle_id)->toBe($this->vehicle->id);
    expect($fuelEntry2->vehicle_id)->toBe($vehicle2->id);
});

test('fuel entry action works with different accounts', function () {
    $account2 = Account::factory()->create(['user_id' => $this->user->id, 'balance' => 5000.00]);

    $data1 = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 2000.00,
        'petrol_station_name' => 'Station 1',
    ];

    $data2 = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $account2->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50500,
        'fuel_quantity' => 50.00,
        'amount' => 3000.00,
        'petrol_station_name' => 'Station 2',
    ];

    $fuelEntry1 = $this->action->execute($data1, $this->user);
    $fuelEntry2 = $this->action->execute($data2, $this->user);

    expect($fuelEntry1->account_id)->toBe($this->account->id);
    expect($fuelEntry2->account_id)->toBe($account2->id);

    // Verify both accounts had their balances updated
    $this->account->refresh();
    $account2->refresh();

    expect($this->account->balance)->toBe(number_format(1000 - 2000, 2, '.', ''));
    expect($account2->balance)->toBe(number_format(5000 - 3000, 2, '.', ''));
});

test('updates vehicle kilometers when fuel entry is created', function () {
    // Set initial kilometers for the vehicle
    $initialKilometers = 45000;
    $this->vehicle->update(['kilometers' => $initialKilometers]);

    $data = [
        'vehicle_id' => $this->vehicle->id,
        'account_id' => $this->account->id,
        'date' => '2024-01-15',
        'odometer_reading' => 50000,
        'fuel_quantity' => 45.50,
        'amount' => 3500.00,
        'petrol_station_name' => 'Shell Station',
    ];

    $fuelEntry = $this->action->execute($data, $this->user);

    // Verify fuel entry was created with correct odometer reading
    expect($fuelEntry->odometer_reading)->toBe(50000);

    // Verify vehicle kilometers were updated
    $this->vehicle->refresh();
    expect($this->vehicle->kilometers)->toBe(50000);
    expect($this->vehicle->kilometers)->not->toBe($initialKilometers);
});
