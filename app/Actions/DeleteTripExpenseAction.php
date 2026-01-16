<?php

namespace App\Actions;

use App\Models\TripExpense;

class DeleteTripExpenseAction
{
    public function execute(TripExpense $tripExpense): void
    {
        $tripExpense->delete();
    }
}
