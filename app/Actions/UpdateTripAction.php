<?php

namespace App\Actions;

use App\Models\Trip;

class UpdateTripAction
{
    public function execute(Trip $trip, array $data, ?array $userIds = null): Trip
    {
        $trip->update($data);

        if ($userIds !== null) {
            $trip->members()->sync($userIds);
        }

        return $trip;
    }
}
