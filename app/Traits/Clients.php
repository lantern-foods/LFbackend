<?php

namespace App\Traits;

use App\Models\Client;

trait Clients
{
    /**
     * Check whether the email address exists
     *
     * @param string $email_address
     * @return bool
     */
    public function emailAddressExists($email_address)
    {
        return Client::where('email_address', $email_address)->exists();
    }

    /**
     * Check whether the phone number exists
     *
     * @param string $phone_number
     * @return bool
     */
    public function phonenoExists($phone_number)
    {
        return Client::where('phone_number', $phone_number)->exists();
    }

    /**
     * Check if the email belongs to the specified client
     *
     * @param int $client_id
     * @param string $email_address
     * @return bool
     */
    public function emailBelongsToClient($client_id, $email_address)
    {
        return Client::where('id', $client_id)
            ->where('email_address', $email_address)
            ->exists();
    }

    /**
     * Check if the phone number belongs to the specified client
     *
     * @param int $client_id
     * @param string $phone_number
     * @return bool
     */
    public function phoneBelongsToClient($client_id, $phone_number)
    {
        return Client::where('id', $client_id)
            ->where('phone_number', $phone_number)
            ->exists();
    }

    /**
     * Get the MSISDN network (phone number's mobile carrier)
     *
     * @param string $msisdn
     * @return array [formatted_msisdn, operator]
     */
    public function get_msisdn_network($msisdn)
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

    /**
     * Placeholder method for meal ratings (to be implemented)
     */
    public function getMealRatings()
    {
        // Placeholder for meal ratings functionality
    }
}
