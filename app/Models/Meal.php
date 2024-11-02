<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $table = 'meals';

    protected $fillable = [
        'cook_id',
        'meal_name',
        'meal_price',
        'min_qty',
        'max_qty',
        'meal_type',
        'prep_time',
        'meal_desc',
        'ingredients',
        'serving_advice',
        'booked_status',
        'express_status',
        'status',
    ];

    /**
     * Define the relationship between Meal and Cook
     * Each meal belongs to one cook.
     */
    public function cook()
    {
        return $this->belongsTo(Cook::class);
    }

    /**
     * Define the relationship between Meal and OrderDetail
     * Each meal can have many order details.
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Define the relationship between Meal and MealImage
     * Each meal can have many associated images.
     */
    public function mealImages()
    {
        return $this->hasMany(MealImage::class);
    }

    /**
     * Define the relationship between Meal and FavoriteMeal
     * Each meal can have many favorites.
     */
    public function favoriteMeals()
    {
        return $this->hasMany(FavoriteMeal::class);
    }

    /**
     * Get the count of favorites for the meal.
     */
    public function favoritesCount()
    {
        return $this->favoriteMeals()
            ->selectRaw('meal_id, count(*) as aggregate')
            ->groupBy('meal_id');
    }

    /**
     * Get the attribute for the number of favorites.
     */
    public function getFavoritesCountAttribute()
    {
        if ($this->relationLoaded('favoritesCount')) {
            return $this->favoritesCount->first()->aggregate ?? 0;
        }

        return $this->favoritesCount()->first()->aggregate ?? 0;
    }

    /**
     * Check if a meal is liked by a specific client.
     */
    public function isLikedBy($client_id)
    {
        return $this->favoriteMeals()->where('client_id', $client_id)->exists();
    }

    /**
     * Cast attributes to appropriate types for consistency.
     */
    protected $casts = [
        'meal_price' => 'float',
        'min_qty' => 'integer',
        'max_qty' => 'integer',
        'booked_status' => 'boolean',
        'express_status' => 'boolean',
        'status' => 'boolean',
    ];
}
