<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Traits\Vehicles;
use Auth;

class VehicleController extends Controller
{
    use Vehicles;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assigned_vehicles = Vehicle::where('deliverycmpy_id',Auth::id())->where('vehicle_status',1)->get();
        $unassigned_vehicles = Vehicle::where('deliverycmpy_id',Auth::id())->where('vehicle_status',0)->get();

    

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => [$assigned_vehicles, $unassigned_vehicles ],
            ];
        
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VehicleRequest $request)
    {
        $request->validated();

        $deliverycmpy_id = $request->input('deliverycmpy_id');
        $license_plate = $request->input('license_plate');
        $make = $request->input('make');
        $model = $request->input('model');
        $description = $request->input('description');

        if ($this->licensePlateExits($license_plate)) {

            $data = [
                'status' => 'error',
                'message' => 'License plate is already in use by another account!',
            ];

            return response()->json($data);
        }

        $vehicle = Vehicle::create([
            'deliverycmpy_id' => $deliverycmpy_id,
            'license_plate' => $license_plate,
            'make' => $make,
            'model' => $model,
            'description' => $description,
        ]);

        if ($vehicle) {

            $data = [
                'status' => 'success',
                'message' => 'Vehicle created successfully.',
            ];
        } else {

            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered. vehicles was NOT created.Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $vehicle = Vehicle::where('id', $id)->first();

        if (!empty($vehicle)) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $vehicle,
            ];
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load vehicle. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, string $id)
    {

        $request->validated();

        $license_plate = $request->input('license_plate');
        $make = $request->input('make');
        $model = $request->input('model');
        $description = $request->input('description');

        if ($this->licensePlateExits($license_plate) && !$this->licenseplateBelongsToVehicle($id, $license_plate)) {

            $data = [
                'status' => 'error',
                'message' => 'License plate is already in use by another account!',
            ];
        }

        $vehicle = Vehicle::where('id',$id)->first();

        if (!empty($vehicle)) {
            
            $vehicle->license_plate = $license_plate;
            $vehicle->make = $make;
            $vehicle->model = $model;

            if ($vehicle->update()) {
                
                $data = [
                    'status' =>"success",
                    'message' => 'Vehicle updated successfully'
                ];
            } else {
                
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered. Vehicle was NOt update. Please try again!',
                ];
            }
            return response()->json($data);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
