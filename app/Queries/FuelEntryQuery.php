<?php

namespace App\Queries;

use App\Models\FuelEntry;
use Illuminate\Database\Eloquent\Builder;

class FuelEntryQuery
{
    public function forVehicle(int $vehicleId): Builder
    {
        return FuelEntry::query()
            ->where('vehicle_id', $vehicleId)
            ->with(['account', 'vehicle'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');
    }
}
