<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

    protected $fillable = [
        'full_name',
        'phone_number',
        'google_map_pin',
        'physical_address',
        'email_address',
        'whatsapp_number',
        'client_otp',
        'password',
    ];

    /**
     * Relationship between Client and Cook
     * A client may have one associated Cook.
     */
    public function cook()
    {
        return $this->hasOne(Cook::class);
    }

    /**
     * Relationship between Client and CustomerAddress
     * A client may have many customer addresses.
     */
    public function customerAddresses()
    {
        return $this->hasMany(Customeraddress::class);
    }

    /**
     * Hide sensitive attributes when serializing the model.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
