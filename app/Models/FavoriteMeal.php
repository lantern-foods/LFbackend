<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteMeal extends Model
{
    use HasFactory;

    protected $table = 'favorite_meals';  // Ensure this matches your actual table name

    protected $fillable = [
        'client_id',
        'meal_id',
    ];

    /**
     * Define the relationship between FavoriteMeal and Meal
     * Each favorite meal record belongs to one meal.
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * Define the relationship between FavoriteMeal and Client
     * Each favorite meal record belongs to one client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
