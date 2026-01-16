<?php

namespace App\Actions;

use App\Models\TripExpense;
use Illuminate\Support\Facades\DB;

class UpdateTripExpenseAction
{
    public function execute(TripExpense $tripExpense, array $data, ?array $sharedWithUserIds = null): TripExpense
    {
        return DB::transaction(function () use ($tripExpense, $data, $sharedWithUserIds) {
            $tripExpense->update($data);

            if (isset($data['is_shared'])) {
                if ($data['is_shared'] && $sharedWithUserIds !== null) {
                    $tripExpense->sharedWith()->sync($sharedWithUserIds);
                } else {
                    $tripExpense->sharedWith()->sync([]);
                }
            }

            return $tripExpense;
        });
    }
}
