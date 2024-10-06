<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteCook extends Model
{
    use HasFactory;

    protected $table = 'favorite_cooks';  // Ensure this matches your actual table name

    protected $fillable = [
        'client_id',
        'cook_id',
    ];

    /**
     * Define the relationship between FavoriteCook and Cook
     * Each favorite cook record belongs to one cook.
     */
    public function cook()
    {
        return $this->belongsTo(Cook::class);
    }

    /**
     * Define the relationship between FavoriteCook and Client
     * Each favorite cook record belongs to one client.
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
