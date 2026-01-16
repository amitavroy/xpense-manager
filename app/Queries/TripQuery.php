<?php

namespace App\Queries;

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TripQuery
{
    public function forUser(): Builder
    {
        return Trip::query()
            ->with(['user', 'members'])
            ->where(function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->orWhereHas('members', function ($q) {
                        $q->where('users.id', Auth::user()->id);
                    });
            })
            ->orderByDesc('start_date');
    }

    public function getTripMembers(): Builder
    {
        return User::query()
            ->select('id', 'name')
            ->where('id', '!=', Auth::user()->id)
            ->orderBy('name');
    }

    public function getExpensesForTrip(Trip $trip): Builder
    {
        return TripExpense::query()
            ->where('trip_id', $trip->id)
            ->with(['paidBy', 'sharedWith'])
            ->orderByDesc('date')
            ->orderByDesc('id');
    }
}
