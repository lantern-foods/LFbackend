<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Cook extends Model
{
    use HasApiTokens, HasFactory, HasRoles;

    protected $fillable = [
        'client_id',
        'kitchen_name',
        'id_number',
        'mpesa_number',
        'alt_phone_number',
        'health_number',
        'health_expiry_date',
        'physical_address',
        'google_map_pin',
        'shrt_desc'
    ];

    /**
     * Define the relationship between Cook and Meal
     */
    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    /**
     * Define the relationship between Cook and Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Define the relationship between Cook and Shift
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
