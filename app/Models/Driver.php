<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

    protected $fillable = [
        'driver_name',
        'email',
        'phone_number',
        'id_number',
        'date_of_birth',
        'gender',
        'drive_otp',
        'password',
        'company_id', 
        'login_status',
        'login_location',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Define the relationship between Driver and Vehicleallocation
     * Each driver has one associated vehicle allocation.
     */
    public function vehicle()
    {
        return $this->hasOne(Vehicleallocation::class);
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
