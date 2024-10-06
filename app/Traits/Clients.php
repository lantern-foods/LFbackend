<?php
namespace App\Traits;

use App\Models\Client;

trait Clients
{
    /*
     * Check whether email Address exits
     */
    public function emailAddressExists($email_address)
    {
        $flag = false;

        $email_address = Client::where('email_address', $email_address)->count();

        if ($email_address > 0) {
            $flag = true;
        }
        return $flag;
    }

    /*
     * Check whether phone number exists
     */
    private function get_msisdn_network($msisdn)
    {
        $regex = [
            'airtel' => '/^\+?(254|0|)7(?:[38]\d{7}|5[0-6]\d{6})\b/',
            'equitel' => '/^\+?(254|0|)76[0-7]\d{6}\b/',
            'safaricom' => '/^\+?(254|0|)(?:7[01249]\d{7}|1[01234]\d{7}|75[789]\d{6}|76[89]\d{6})\b/',
            'telkom' => '/^\+?(254|0|)7[7]\d{7}\b/',
        ];

        foreach ($regex as $operator => $re) {
            if (preg_match($re, $msisdn)) {
                return [preg_replace('/^\+?(254|0)/', '254', $msisdn), $operator];
            }
        }
        return [false, false];
    }

    public function phonenoExists($phone_number)
    {
        $flag = false;

        $phone_number_exist = Client::where('phone_number', $phone_number)->count();
        return $phone_number_exist;
        if ($phone_number > 0) {
            $flag = true;
        }
        return $flag;
    }

    /*
     * Check if email belongs to the client
     */
    public function emailBelongsToClient($client_id, $email_address)
    {
        $email_belongs_to_client = false;

        $client = Client::where('id', $client_id)->where('email_address', $email_address)->count();

        if ($client > 0) {
            $email_belongs_to_client = true;
        }

        return $email_belongs_to_client;
    }

    /*
     * Check if phone number belongs to the client
     */
    public function phoneBelongsToClient($client_id, $phone_number)
    {
        $phonenumber_belongs_to_client = false;

        $client = Client::where('id', $client_id)->where('phone_number', $phone_number)->count();

        if ($client > 0) {
            $phonenumber_belongs_to_client = true;
        }
        return $phonenumber_belongs_to_client;
    }
    public  function  getMealRatings()
    {
//

    }
}
