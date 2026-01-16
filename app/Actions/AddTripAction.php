<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\User;

class AddTripAction
{
    public function execute(array $data, User $user, ?array $userIds = null): Trip
    {
        $data['user_id'] = $user->id;

        $trip = Trip::create($data);

        if ($userIds !== null) {
            $trip->members()->sync($userIds);
        }

        return $trip;
    }
}
