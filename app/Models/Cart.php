<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';  // Ensure this matches your actual table name

    protected $fillable = [
        'client_id',
        'meal_id',
        'package_id',
        'quantity',
        'subtotal',
        'shift_id',
    ];

    /**
     * Define the relationship between Cart and Package
     * Each Cart entry may be associated with one Package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Define the relationship between Cart and Meal
     * Each Cart entry may be associated with one Meal.
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
