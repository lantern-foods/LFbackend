<?php

namespace App\Traits;

use App\Models\Driver;

trait Drivers
{
    /**
     * Check whether email address exists.
     */
    public function emailAddressExists($email): bool
    {
        return Driver::where('email', $email)->exists();
    }

    /**
     * Check whether phone number exists.
     */
    public function phonenoExists($phone_number): bool
    {
        return Driver::where('phone_number', $phone_number)->exists();
    }

    /**
     * Check whether ID number exists.
     */
    public function idnumberExists($id_number): bool
    {
        return Driver::where('id_number', $id_number)->exists();
    }

    /**
     * Check if the email belongs to the specified driver.
     */
    public function emailBelongsToDriver($driver_id, $email): bool
    {
        return Driver::where('id', $driver_id)
                     ->where('email', $email)
                     ->exists();
    }

    /**
     * Check if the phone number belongs to the specified driver.
     */
    public function phoneBelongsToDriver($driver_id, $phone_number): bool
    {
        return Driver::where('id', $driver_id)
                     ->where('phone_number', $phone_number)
                     ->exists();
    }

    /**
     * Check if the ID number belongs to the specified driver.
     */
    public function idnumberBelongsToDriver($driver_id, $id_number): bool
    {
        return Driver::where('id', $driver_id)
                     ->where('id_number', $id_number)
                     ->exists();
    }
}
