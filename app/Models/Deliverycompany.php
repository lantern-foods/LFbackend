<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DeliveryCompany extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

    protected $table = 'delivery_companies';  // Table name in your database

    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'company',
        'password',
        'delvry_otp',
        'location_charge',
    ];

    /**
     * The attributes that should be hidden for serialization.
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
