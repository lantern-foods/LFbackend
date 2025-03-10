<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageDetail extends Model
{
    use HasFactory;

    protected $table = 'packages_details';  // Ensure this matches the actual table name

    protected $fillable = [
        'package_id',
        'meal_id',
    ];

    /**
     * Define the relationship between Packagedetail and Package
     * Each package detail is associated with one package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Define the relationship between Packagedetail and Meal
     * Each package detail is associated with one meal.
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }

    /**
     * Disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = false;  // Set to true if your table includes timestamps
}
