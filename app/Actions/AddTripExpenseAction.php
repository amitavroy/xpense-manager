<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddTripExpenseAction
{
    public function execute(array $data, Trip $trip, User $user, ?array $sharedWithUserIds = null): TripExpense
    {
        return DB::transaction(function () use ($data, $trip, $user, $sharedWithUserIds) {
            $data['trip_id'] = $trip->id;
            $data['paid_by'] = $user->id;

            $expense = TripExpense::create($data);

            if ($data['is_shared'] && $sharedWithUserIds !== null) {
                $expense->sharedWith()->sync($sharedWithUserIds);
            }

            return $expense;
        });
    }
}
