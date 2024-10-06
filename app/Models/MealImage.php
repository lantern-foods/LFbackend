<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealImage extends Model
{
    use HasFactory;

    // Ensure the correct table name is used, assuming 'meals_images'
    protected $table = 'meals_images';

    // Define mass-assignable fields to protect against mass assignment vulnerabilities
    protected $fillable = [
        'meal_id',
        'image_url',
    ];

    /**
     * Define the relationship between MealImage and Meal
     * Each image belongs to one meal.
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
