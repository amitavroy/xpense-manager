<?php

namespace App\Actions;

use App\Models\Trip;

class DeleteTripAction
{
    public function execute(Trip $trip): void
    {
        $trip->delete();
    }
}
