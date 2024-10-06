<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageMeal extends Model
{
    use HasFactory;

    protected $table = 'package_meals';  // Ensure this matches your actual table name

    protected $fillable = [
        'package_id',
        'meal_id',
        'quantity',
    ];

    /**
     * Define the relationship between PackageMeal and Package
     * Each PackageMeal belongs to one Package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Define the relationship between PackageMeal and Meal
     * Each PackageMeal belongs to one Meal.
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id', 'id');
    }

    /**
     * Disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = false;  // Set to true if your table includes timestamps
}
