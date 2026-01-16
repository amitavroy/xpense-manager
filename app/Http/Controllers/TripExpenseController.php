<?php

namespace App\Http\Controllers;

use App\Actions\AddTripExpenseAction;
use App\Actions\DeleteTripExpenseAction;
use App\Actions\UpdateTripExpenseAction;
use App\Http\Requests\StoreTripExpenseRequest;
use App\Http\Requests\UpdateTripExpenseRequest;
use App\Models\Trip;
use App\Models\TripExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TripExpenseController extends Controller
{
    private function authorizeTripAccess(Trip $trip): void
    {
        abort_if(! $trip->isAccessibleBy(), 403);
    }

    public function index(Trip $trip): Response
    {
        $this->authorizeTripAccess($trip);

        $expenses = $trip->expenses()
            ->with(['paidBy', 'sharedWith'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('trips/expenses/index', [
            'trip' => $trip,
            'expenses' => $expenses,
        ]);
    }

    public function store(StoreTripExpenseRequest $request, Trip $trip, AddTripExpenseAction $addTripExpenseAction): RedirectResponse
    {
        $this->authorizeTripAccess($trip);

        $data = $request->validated();
        $sharedWithUserIds = $data['shared_with'] ?? [];
        unset($data['shared_with']);

        $addTripExpenseAction->execute($data, $trip, Auth::user(), $sharedWithUserIds);

        return redirect()->route('trips.show', $trip);
    }

    public function show(Trip $trip, TripExpense $tripExpense): Response
    {
        abort_if($tripExpense->trip_id !== $trip->id, 404);
        $this->authorizeTripAccess($trip);

        $tripExpense->load(['paidBy', 'sharedWith']);

        return Inertia::render('trips/expenses/show', [
            'trip' => $trip,
            'expense' => $tripExpense,
        ]);
    }

    public function update(UpdateTripExpenseRequest $request, Trip $trip, TripExpense $tripExpense, UpdateTripExpenseAction $updateTripExpenseAction): RedirectResponse
    {
        abort_if($tripExpense->trip_id !== $trip->id, 404);
        $this->authorizeTripAccess($trip);

        $data = $request->validated();
        $sharedWithUserIds = $data['shared_with'] ?? [];
        unset($data['shared_with']);

        $updateTripExpenseAction->execute($tripExpense, $data, $sharedWithUserIds);

        return redirect()->route('trips.show', $trip);
    }

    public function destroy(Trip $trip, TripExpense $tripExpense, DeleteTripExpenseAction $deleteTripExpenseAction): RedirectResponse
    {
        abort_if($tripExpense->trip_id !== $trip->id, 404);
        $this->authorizeTripAccess($trip);

        $deleteTripExpenseAction->execute($tripExpense);

        return redirect()->route('trips.show', $trip);
    }
}
