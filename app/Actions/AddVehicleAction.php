<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Vehicle;

class AddVehicleAction
{
    public function execute(array $data, User $user): Vehicle
    {
        $data['user_id'] = $user->id;
        $data['kilometers'] = $data['kilometers'] ?? 0;

        return Vehicle::create($data);
    }
}
