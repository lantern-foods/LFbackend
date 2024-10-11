<?php
namespace App\Traits;

use App\Models\Deliverycompany;

trait Deliverycompanies
{
    /**
     * Check if email address exists in the delivery companies.
     */
    public function emailAddressExists($email): bool
    {
        return Deliverycompany::where('email', $email)->exists();
    }

    /**
     * Check if phone number exists in the delivery companies.
     */
    public function phonenoExists($phone_number): bool
    {
        return Deliverycompany::where('phone_number', $phone_number)->exists();
    }

    /**
     * Check if the email belongs to the specified delivery company.
     */
    public function emailBelongsToDeliverycompany($deliverycompany_id, $email_address): bool
    {
        return Deliverycompany::where('id', $deliverycompany_id)
            ->where('email', $email_address)
            ->exists();
    }

    /**
     * Check if the phone number belongs to the specified delivery company.
     */
    public function phoneBelongsToDeliverycompany($deliverycompany_id, $phone_number): bool
    {
        return Deliverycompany::where('id', $deliverycompany_id)
            ->where('phone_number', $phone_number)
            ->exists();
    }
}
