<?php

namespace App\Traits;

trait GlobalFunctions
{
    /**
     * Format a phone number to international format (Kenyan phone numbers).
     */
    public function formatPhoneNumber($phone_no)
    {
        // Validate the phone number prefix (should start with '07' or '01')
        if (preg_match('/^(07|01)\d{8}$/', $phone_no)) {
            $prefix = substr($phone_no, 0, 2);
            $formatted_number = '254' . substr($phone_no, 1);
            return $formatted_number;
        }

        // If validation fails, return 'Invalid'
        return 'Invalid';
    }
}
