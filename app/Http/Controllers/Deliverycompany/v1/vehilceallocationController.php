<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicleallocation;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class vehilceallocationController extends Controller
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
            'driver_id' => 'required',
            'vehicle_id' => 'required',
        ]);

        DB::beginTransaction();

        try {
            // Check if the driver already has a vehicle allocated
            $allocation = Vehicleallocation::where('driver_id', $request->input('driver_id'))->first();

            if ($allocation) {
                // Update the existing allocation if the driver already has a vehicle
                $allocation->vehicle_id = $request->input('vehicle_id');
                $allocation->save();

                $data = [
                    'status' => 'success',
                    'message' => 'Driver allocated successfully',
                   
                ];
            } else {
                // Create a new allocation if the driver doesn't have a vehicle yet
                Vehicleallocation::create([
                    'driver_id' => $request->input('driver_id'),
                    'vehicle_id' => $request->input('vehicle_id'),
                ]);

                $data = [
                    'status' => 'success',
                    'message' => 'Driver allocated successfully',
                   
                ];
            }

            // Update the vehicle's status to 1 (allocated)
            $vehicle = Vehicle::find($request->vehicle_id);
            $vehicle->vehicle_status = 1; // Assuming 'status' is the column name for the vehicle's allocation status
            $vehicle->save();

            DB::commit();

            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollBack();
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, Driver was NOT created. Please try again!',
                'data' => $e
            ];
            return response()->json($data);
        }
    }

    public function allocatedDrivers()
    {
        $allocated_drivers = Driver::with('vehicle')->get();

        $driversWithoutVehicles = Driver::doesntHave('vehicle')->get();

        return $allocated_drivers;
    }
}
