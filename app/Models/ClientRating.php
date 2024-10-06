<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'timeless_rating',
        'courtesy_rating',
        'delivery_directions',
        'comment',
    ];

    /**
     * Define the relationship between ClientRating and Order
     * Each client rating is associated with one order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Optionally cast rating fields to integer for consistency
     */
    protected $casts = [
        'timeless_rating' => 'integer',
        'courtesy_rating' => 'integer',
    ];

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
