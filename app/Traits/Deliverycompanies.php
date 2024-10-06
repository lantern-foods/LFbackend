<?php
namespace App\Traits;

use App\Models\Deliverycompany;

trait Deliverycompanies
{
    /*
     *
     * Check whethwer email Address exits
     */
    public function emailAddressExists($email)
    {
        $flag = false;

        $email_address = Deliverycompany::where('email', $email)->count();

        if ($email_address > 0) {
            $flag = true;
        }
        return $flag;
    }
    /*
    * Check whether phone number exists
    */
    public function phonenoExists($phone_number)
    {
        $flag = false;

        $phone_number=Deliverycompany::where('phone_number',$phone_number)->count();
    }

    /*
     *Check if email belongs to the delivery company
     */
    public function emailBelongsToDeliverycompany($deliverycompany_id,$email_address)
    {
        $email_belongs_to_deliverycompany=false;

        $deliverycompany=Deliverycompany::where('id',$deliverycompany_id)->where('email',$email_address)->count();

        if ($deliverycompany>0) {
            $email_belongs_to_deliverycompany=true;
        }

        return $email_belongs_to_deliverycompany;
    }

    /*
     *Check if phone number belongs to the delivery company
     */
    public function phoneBelongsToDeliverycompany($deliverycompany_id,$phone_number)
    {
        $phonenumber_belongs_to_deliverycompany=false;

        $deliverycompany=Deliverycompany::where('id',$deliverycompany_id)->where('phone_number',$phone_number)->count();

        if ($deliverycompany>0) {
            $phonenumber_belongs_to_deliverycompany=true;
        }
        return $phonenumber_belongs_to_deliverycompany;
    }


}
