<?php

namespace App\Queries;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VehicleQuery
{
    public function forUser(): Builder
    {
        return Vehicle::query()
            ->where('user_id', Auth::user()->id)
            ->orderBy('name');
    }
}
