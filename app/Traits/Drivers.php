<?php
namespace App\Traits;

use App\Models\Driver;

trait Drivers
{

    /***
     * Check whether email adddress exist
     */
    public function emailAddressExists($email)
    {
        $flag = false;

        $email = Driver::where('email', $email)->count();

        if ($email > 0) {
            $flag = true;
        }
        return $flag;
    }

    /***
     * Check whether phone number exits
     */
    public function phonenoExists($phone_number)
    {
        $flag = false;

        $phone_number = Driver::Where('phone_number', $phone_number)->count();

        if ($phone_number > 0) {
            $flag = true;
        }
        return $flag;
    }

    /**
     * Check if id number exits
     */
    public function idnumberExists($id_number)
    {
        $flag = false;

        $id_number = Driver::where('id_number', $id_number)->count();

        if ($id_number > 0) {

            $flag = true;
        }
        return $flag;
    }

    /**
     * Check whether if id number belongs to driver
     *
     */
    public function emailBelongsToDriver($driver_id, $email)
    {
        $email_belongs_to_driver = false;

        $driver = Driver::where('id', $driver_id)->where('email', $email)->count();

        if ($driver > 0) {
            $email_belongs_to_driver=true;
        }
        return $email_belongs_to_driver;
    }

    /***
     * Check if phone number belongs to driver
     */
    public function phoneBelongsToDriver($driver_id, $phone_number)
    {
        $phonenumber_belongs_to_driver=false;

        $driver = Driver::where('id', $driver_id)->where('phone_number',$phone_number)->count();

        if ($driver > 0) {
            $phonenumber_belongs_to_driver=true;

        }
        return $phonenumber_belongs_to_driver;
    }

    /**
     * Check if id number belongs to driver
     */
    public function idnumberBelongsToDriver($driver_id,$id_number)
    {
        $idnumber_belongs_to_driver=false;

        $driver = Driver::where('id',$driver_id)->where('id_number',$id_number)->count();

        if ($driver > 0) {
            
            $idnumber_belongs_to_driver=true;
        }
        return $idnumber_belongs_to_driver;
    }

}
