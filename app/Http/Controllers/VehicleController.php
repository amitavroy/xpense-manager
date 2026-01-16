<?php

namespace App\Http\Controllers;

use App\Actions\AddVehicleAction;
use App\Actions\ArchiveVehicleAction;
use App\Actions\UpdateVehicleAction;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Queries\FuelEntryQuery;
use App\Queries\VehicleQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function __construct(
        private readonly VehicleQuery $vehicleQuery,
        private readonly FuelEntryQuery $fuelEntryQuery
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $vehicles = $this->vehicleQuery
            ->forUser()
            ->paginate(10);

        return Inertia::render('vehicles/index', [
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $vehicle = new Vehicle;

        return Inertia::render('vehicles/create', [
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreVehicleRequest $request,
        AddVehicleAction $addVehicleAction
    ): RedirectResponse {
        $data = $request->validated();

        $vehicle = $addVehicleAction->execute($data, Auth::user());

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Vehicle created successfully!',
        ]);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle): Response
    {
        abort_if($vehicle->user_id !== Auth::user()->id, 403);

        return Inertia::render('vehicles/edit', [
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle): Response
    {
        abort_if($vehicle->user_id !== Auth::user()->id, 403);

        $fuelEntries = $this->fuelEntryQuery
            ->forVehicle($vehicle->id)
            ->get();

        return Inertia::render('vehicles/show', [
            'vehicle' => $vehicle,
            'fuelEntries' => $fuelEntries,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateVehicleRequest $request,
        Vehicle $vehicle,
        UpdateVehicleAction $updateVehicleAction
    ): RedirectResponse {
        $data = $request->validated();

        $updateVehicleAction->execute($vehicle, $data);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Vehicle updated successfully!',
        ]);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle, ArchiveVehicleAction $archiveVehicleAction): RedirectResponse
    {
        abort_if($vehicle->user_id !== Auth::user()->id, 403);

        $archiveVehicleAction->execute($vehicle);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Vehicle archived successfully!',
        ]);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle archived successfully');
    }
}
