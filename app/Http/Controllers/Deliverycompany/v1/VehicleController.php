<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Traits\Vehicles;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    use Vehicles;

    /**
     * Display a listing of vehicles (assigned and unassigned).
     */
    public function index()
    {
        $assignedVehicles = Vehicle::where('deliverycmpy_id', Auth::id())
            ->where('vehicle_status', 1)
            ->get();

        $unassignedVehicles = Vehicle::where('deliverycmpy_id', Auth::id())
            ->where('vehicle_status', 0)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful',
            'data' => [
                'assigned_vehicles' => $assignedVehicles,
                'unassigned_vehicles' => $unassignedVehicles
            ],
        ]);
    }

    /**
     * Store a newly created vehicle in storage.
     */
    public function store(VehicleRequest $request)
    {
        $request->validated();

        $licensePlate = $request->input('license_plate');

        if ($this->licensePlateExits($licensePlate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'License plate is already in use by another vehicle!',
            ], 400);
        }

        $vehicle = Vehicle::create([
            'deliverycmpy_id' => $request->input('deliverycmpy_id'),
            'license_plate' => $licensePlate,
            'make' => $request->input('make'),
            'model' => $request->input('model'),
            'description' => $request->input('description'),
        ]);

        if ($vehicle) {
            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle created successfully.',
            ], 201);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered. Vehicle was NOT created. Please try again!',
        ], 500);
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit(string $id)
    {
        $vehicle = Vehicle::find($id);

        if ($vehicle) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $vehicle,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Unable to load vehicle. Please try again!',
        ], 404);
    }

    /**
     * Update the specified vehicle in storage.
     */
    public function update(UpdateVehicleRequest $request, string $id)
    {
        $request->validated();

        $licensePlate = $request->input('license_plate');

        if ($this->licensePlateExits($licensePlate) && !$this->licenseplateBelongsToVehicle($id, $licensePlate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'License plate is already in use by another vehicle!',
            ], 400);
        }

        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'Vehicle not found!',
            ], 404);
        }

        $vehicle->license_plate = $licensePlate;
        $vehicle->make = $request->input('make');
        $vehicle->model = $request->input('model');
        $vehicle->description = $request->input('description');

        if ($vehicle->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle updated successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered. Vehicle was NOT updated. Please try again!',
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'Vehicle not found!',
            ], 404);
        }

        if ($vehicle->delete()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle deleted successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered. Vehicle was NOT deleted. Please try again!',
        ], 500);
    }
}
