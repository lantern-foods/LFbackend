<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    use HasFactory;

    protected $table = 'drivers_documents';  // Ensure this matches your actual table name

    protected $fillable = [
        'driver_id',
        'profile_pic',
        'id_front',
        'id_back',
    ];

    /**
     * Define the relationship between DriverDocument and Driver
     * Each driver document belongs to one driver.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
