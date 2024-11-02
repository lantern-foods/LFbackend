<?php

namespace App\Traits;

use App\Models\Client;

trait Clients
{
    /**
     * Sanitize phone number to remove special characters and normalize it to '254' format.
     *
     * @param string $phone_number
     * @return string
     */
    private function sanitizePhoneNumber($phone_number)
    {
        // Remove all non-numeric characters
        $cleaned_number = preg_replace('/\D/', '', $phone_number);

        // If it starts with 07 or 01, replace with 254
        if (preg_match('/^0[17]/', $cleaned_number)) {
            $cleaned_number = preg_replace('/^0/', '254', $cleaned_number);
        }

        // Ensure it starts with 254
        if (!preg_match('/^254/', $cleaned_number)) {
            return ''; // Invalid format
        }

        return $cleaned_number;
    }

    /**
     * Check whether the email address exists
     */
    public function emailAddressExists($email_address)
    {
        return Client::where('email_address', $email_address)->exists();
    }

    /**
     * Check whether the phone number exists
     */
    public function phonenoExists($phone_number)
    {
        // Sanitize the phone number
        $phone_number = $this->sanitizePhoneNumber($phone_number);

        if (!$phone_number) {
            return false; // Return false if phone number is invalid
        }

        return Client::where('phone_number', $phone_number)->exists();
    }

    /**
     * Check if the email belongs to the specified client
     */
    public function emailBelongsToClient($client_id, $email_address)
    {
        return Client::where('id', $client_id)
            ->where('email_address', $email_address)
            ->exists();
    }

    /**
     * Check if the phone number belongs to the specified client
     */
    public function phoneBelongsToClient($client_id, $phone_number)
    {
        // Sanitize the phone number
        $phone_number = $this->sanitizePhoneNumber($phone_number);

        if (!$phone_number) {
            return false; // Return false if phone number is invalid
        }

        return Client::where('id', $client_id)
            ->where('phone_number', $phone_number)
            ->exists();
    }

    /**
     * Placeholder method for meal ratings (to be implemented)
     */
    public function getMealRatings()
    {
        // Placeholder for meal ratings functionality
    }
}
