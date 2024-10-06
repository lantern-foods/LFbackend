<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicleallocation extends Model
{
    use HasFactory;

    protected $table = 'vehicle_allocation';  // Ensure this matches your actual table name

    protected $fillable = [
        'driver_id',
        'vehicle_id',
    ];

    /**
     * Define the relationship between Vehicleallocation and Driver
     * Each vehicle allocation belongs to one driver.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Define the relationship between Vehicleallocation and Vehicle
     * Each vehicle allocation belongs to one vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
