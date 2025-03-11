<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $model;

    protected $fillable = [
        'deliverycmpy_id',
        'license_plate',
        'make',
        'model',
        'description',
        'vehicle_status',
    ];

    /**
     * Define the relationship between Vehicle and DeliveryCompany
     * Each vehicle belongs to one delivery company.
     */
    public function deliveryCompany()
    {
        return $this->belongsTo(DeliveryCompany::class, 'deliverycmpy_id');
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
