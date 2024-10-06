<?php

namespace App\Traits;

use App\Models\Vehicle;

trait Vehicles
{

    /***
     * check whether license plate exits
     * 
     */
    public function licensePlateExits($license_plate)
    {
        $flag = false;

        $license_plate = Vehicle::where('license_plate',$license_plate)->count();

        if ($license_plate > 0) {
            
            $flag = true;
        }
        return $flag;
    }

    /**
     * check if license plate belongs to the vehicle
     */
    public function licenseplateBelongsToVehicle($vehicle_id, $license_plate)
    {
        $license_plate_belongs_to_vehicle=false;

        $vehicle = Vehicle::where('id',$vehicle_id)->where('license_plate',$license_plate)->count();

        if ($vehicle > 0) {
            

            $license_plate_belongs_to_vehicle = true;
        }
        return $license_plate_belongs_to_vehicle;
    }

}