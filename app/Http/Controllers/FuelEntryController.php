<?php

namespace App\Http\Controllers;

use App\Actions\AddFuelEntryAction;
use App\Http\Requests\StoreFuelEntryRequest;
use App\Models\Account;
use App\Models\FuelEntry;
use App\Models\Vehicle;
use App\Queries\VehicleQuery;
use App\Services\DropdownService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FuelEntryController extends Controller
{
    public function __construct(
        private readonly DropdownService $dropdownService,
        private readonly VehicleQuery $vehicleQuery
    ) {}

    /**
     * Show the form for creating a new fuel entry.
     */
    public function create(): Response
    {
        $fuelEntry = new FuelEntry;
        $vehicles = $this->vehicleQuery
            ->forUser()
            ->get(['id', 'name', 'kilometers'])
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'kilometers' => $vehicle->kilometers,
                ];
            });
        $accounts = $this->dropdownService->getAccounts(Auth::user());

        $vehicleId = request()->integer('vehicle_id');

        if ($vehicleId !== 0) {
            $vehicle = $this->vehicleQuery
                ->forUser()
                ->where('id', $vehicleId)
                ->first();

            if ($vehicle !== null) {
                $fuelEntry->vehicle_id = $vehicle->id;
            }
        }

        return Inertia::render('fuel-entry/create', [
            'fuelEntry' => $fuelEntry,
            'vehicles' => $vehicles,
            'accounts' => $accounts,
            'vehicleId' => $fuelEntry->vehicle_id,
        ]);
    }

    /**
     * Store a newly created fuel entry in storage.
     */
    public function store(
        StoreFuelEntryRequest $request,
        AddFuelEntryAction $addFuelEntryAction
    ): RedirectResponse {
        $data = $request->validated();

        // Verify vehicle belongs to user
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        abort_if($vehicle->user_id !== Auth::user()->id, 403);

        // Verify account belongs to user
        $account = Account::findOrFail($data['account_id']);
        abort_if($account->user_id !== Auth::user()->id, 403);

        $fuelEntry = $addFuelEntryAction->execute($data, Auth::user());

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Fuel entry created successfully!',
        ]);

        // Redirect to vehicle show page if vehicle_id is present
        if (isset($data['vehicle_id'])) {
            return redirect()->route('vehicles.show', $vehicle);
        }

        return redirect()->route('fuel-entry.create');
    }
}
