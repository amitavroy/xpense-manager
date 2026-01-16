<?php

namespace App\Actions;

use App\Models\Vehicle;

class UpdateVehicleAction
{
    public function execute(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);

        return $vehicle;
    }
}
