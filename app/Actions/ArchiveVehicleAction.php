<?php

namespace App\Actions;

use App\Models\Vehicle;

class ArchiveVehicleAction
{
    public function execute(Vehicle $vehicle): void
    {
        $vehicle->delete();
    }
}
