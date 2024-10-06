<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookRating extends Model
{
    use HasFactory;

    // Specify the table if it's not the plural form of the model name
    protected $table = 'cook_ratings';

    // Define mass assignable attributes
    protected $fillable = [
        'order_id',
        'cook_id',  // Assuming each rating is tied to a specific cook
        'timeless_rating',
        'courtesy_rating',
        'packaging',
        'comment',
    ];

    /**
     * Define the relationship between CookRating and Order
     * Each rating belongs to one order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Define the relationship between CookRating and Cook
     * Each rating belongs to one cook
     */
    public function cook()
    {
        return $this->belongsTo(Cook::class);
    }

    /**
     * Disable timestamps if not needed
     * Remove if you want to keep the default Laravel behavior (created_at, updated_at)
     */
    public $timestamps = true;

    /**
     * Casting ratings to integer for strict type validation
     */
    protected $casts = [
        'timeless_rating' => 'integer',
        'courtesy_rating' => 'integer',
        'packaging' => 'integer',
    ];
}
