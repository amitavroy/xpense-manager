<?php

namespace App\Console\Commands;

use App\Models\FuelEntry;
use Illuminate\Console\Command;

class CalculateAvgFuelConsumption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:calculate-avg-fuel-consumption';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill distance since last refuel and average km/L for existing fuel entries';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $vehicleIds = FuelEntry::query()
            ->distinct()
            ->pluck('vehicle_id');

        foreach ($vehicleIds as $vehicleId) {
            $previousEntry = null;

            $entries = FuelEntry::query()
                ->where('vehicle_id', $vehicleId)
                ->orderBy('odometer_reading')
                ->get();

            foreach ($entries as $entry) {
                $metrics = FuelEntry::computeMetrics(
                    $previousEntry,
                    $entry->odometer_reading,
                    (float) $entry->fuel_quantity,
                );

                $entry->forceFill($metrics);
                $entry->save();

                $previousEntry = $entry;
            }
        }

        $this->info('Fuel entry metrics backfilled successfully.');
    }
}
