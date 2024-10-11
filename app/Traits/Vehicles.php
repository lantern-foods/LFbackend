<?php

namespace App\Traits;

use App\Models\Vehicle;

trait Vehicles
{
    /**
     * Check whether a license plate exists.
     *
     * @param string $licensePlate
     * @return bool
     */
    public function licensePlateExists(string $licensePlate): bool
    {
        return Vehicle::where('license_plate', $licensePlate)->exists();
    }

    /**
     * Check if the given license plate belongs to the specified vehicle.
     *
     * @param int $vehicleId
     * @param string $licensePlate
     * @return bool
     */
    public function licensePlateBelongsToVehicle(int $vehicleId, string $licensePlate): bool
    {
        return Vehicle::where('id', $vehicleId)
            ->where('license_plate', $licensePlate)
            ->exists();
    }
}
