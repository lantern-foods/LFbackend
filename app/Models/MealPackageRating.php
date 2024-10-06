<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealPackageRating extends Model
{
    use HasFactory;

    protected $table = 'meal_package_ratings';

    protected $fillable = [
        'meal_id',
        'package_id',
        'user_id',
        'packaging',
        'taste',
        'service',
        'review',
    ];

    /**
     * Define the relationship between MealPackageRating and Meal
     * Each rating is associated with one meal.
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }

    /**
     * Define the relationship between MealPackageRating and Package
     * Each rating is associated with one package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Define the relationship between MealPackageRating and User
     * Each rating is given by one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Optionally cast the rating fields to integers for consistency.
     */
    protected $casts = [
        'packaging' => 'integer',
        'taste' => 'integer',
        'service' => 'integer',
    ];

    /**
     * Disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * By default, Laravel assumes the timestamps are present.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
