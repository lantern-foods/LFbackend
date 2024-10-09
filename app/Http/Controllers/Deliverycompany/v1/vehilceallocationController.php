<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicleallocation;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class VehicleAllocationController extends Controller
{
    /**
     * Allocate a vehicle to a driver and update the vehicle's status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function allocate(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        DB::beginTransaction();

        try {
            $driverId = $request->input('driver_id');
            $vehicleId = $request->input('vehicle_id');

            // Check if the driver already has a vehicle allocated
            $allocation = Vehicleallocation::where('driver_id', $driverId)->first();

            if ($allocation) {
                // Update existing allocation
                $allocation->vehicle_id = $vehicleId;
                $allocation->save();
                $message = 'Driver reallocated to a new vehicle successfully.';
            } else {
                // Create a new allocation
                Vehicleallocation::create([
                    'driver_id' => $driverId,
                    'vehicle_id' => $vehicleId,
                ]);
                $message = 'Driver allocated to vehicle successfully.';
            }

            // Update the vehicle's status to 'allocated' (assuming 1 is for allocated)
            $vehicle = Vehicle::findOrFail($vehicleId);
            $vehicle->vehicle_status = 1; // Mark as allocated
            $vehicle->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'A problem was encountered. The vehicle allocation failed. Please try again!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a list of all drivers with and without vehicles.
     *
     * @return \Illuminate\Http\Response
     */
    public function allocatedDrivers()
    {
        $allocatedDrivers = Driver::with('vehicle')->get();
        $driversWithoutVehicles = Driver::doesntHave('vehicle')->get();

        return response()->json([
            'status' => 'success',
            'allocated_drivers' => $allocatedDrivers,
            'drivers_without_vehicles' => $driversWithoutVehicles,
        ]);
    }
}
