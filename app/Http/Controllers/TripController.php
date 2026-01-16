<?php

namespace App\Http\Controllers;

use App\Actions\AddTripAction;
use App\Actions\DeleteTripAction;
use App\Actions\UpdateTripAction;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Queries\TripQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TripController extends Controller
{
    public function __construct(
        private readonly TripQuery $tripQuery
    ) {}

    public function index(): Response
    {
        $trips = $this->tripQuery
            ->forUser()
            ->paginate(10);

        return Inertia::render('trips/index', [
            'trips' => $trips,
        ]);
    }

    public function create(): Response
    {
        $users = $this->tripQuery
            ->getTripMembers()
            ->orderBy('name')
            ->get();

        $trip = new Trip;

        return Inertia::render('trips/create', [
            'trip' => $trip,
            'users' => $users,
        ]);
    }

    public function store(StoreTripRequest $request, AddTripAction $addTripAction): RedirectResponse
    {
        $data = $request->validated();
        $userIds = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $trip = $addTripAction->execute($data, Auth::user(), $userIds);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Trip created successfully!',
        ]);

        return redirect()->route('trips.show', $trip);
    }

    public function show(Trip $trip): Response
    {
        abort_if(! $trip->isAccessibleBy(), 403);

        $trip->load(['user', 'members']);

        $expenses = $this->tripQuery->getExpensesForTrip($trip)->get();

        $currentUserId = Auth::user()->id;

        $stats = [
            'totalExpensesByUser' => $trip->getTotalExpensesByUser($currentUserId),
            'totalSharedExpenses' => $trip->getTotalSharedExpensesForUser($currentUserId),
            'totalNonSharedExpenses' => $trip->getTotalNonSharedExpensesForUser($currentUserId),
        ];

        $tripMembers = $trip->members()->select('users.id', 'users.name')->get();
        $tripMembers->prepend($trip->user->only(['id', 'name']));

        return Inertia::render('trips/show', [
            'trip' => $trip,
            'expenses' => $this->formatExpenses($expenses),
            'stats' => $stats,
            'tripMembers' => $tripMembers->unique('id')->values(),
        ]);
    }

    /**
     * @param  Collection<int, TripExpense>  $expenses
     * @return array<int, array<string, mixed>>
     */
    private function formatExpenses(Collection $expenses): array
    {
        return $expenses->map(function ($expense) {
            return [
                ...$expense->toArray(),
                'paid_by_user' => $expense->paidBy ? [
                    'id' => $expense->paidBy->id,
                    'name' => $expense->paidBy->name,
                ] : null,
                'shared_with' => $expense->sharedWith->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    public function update(UpdateTripRequest $request, Trip $trip, UpdateTripAction $updateTripAction): RedirectResponse
    {
        abort_if($trip->user_id !== Auth::user()->id, 403);

        $data = $request->validated();
        $userIds = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $updateTripAction->execute($trip, $data, $userIds);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Trip updated successfully!',
        ]);

        return redirect()->route('trips.show', $trip);
    }

    public function destroy(Trip $trip, DeleteTripAction $deleteTripAction): RedirectResponse
    {
        abort_if($trip->user_id !== Auth::user()->id, 403);

        $deleteTripAction->execute($trip);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Trip deleted successfully!',
        ]);

        return redirect()->route('trips.index');
    }
}
