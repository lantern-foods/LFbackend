<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'cook_id',
        'order_id',
        'driver_id',
        'status',  // Corrected typo from 'sttaus' to 'status'
    ];

    /**
     * Define the relationship between Collection and Cook
     * Each collection belongs to one cook.
     */
    public function cook()
    {
        return $this->belongsTo(Cook::class);
    }

    /**
     * Define the relationship between Collection and Order
     * Each collection is associated with one order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Define the relationship between Collection and Driver
     * Each collection is assigned to one driver.
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
