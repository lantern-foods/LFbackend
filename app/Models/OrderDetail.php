<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    protected $fillable = [
        'meal_id',
        'order_id',
        'qty',
        'unit_price',
        'subtotal',
        'shift_id',
        'package_id'
    ];

    /**
     * Define the relationship between OrderDetail and Meal
     * Each order detail refers to one meal.
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * Define the relationship between OrderDetail and Order
     * Each order detail belongs to one order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Define the relationship between OrderDetail and Shift
     * Each order detail is linked to one shift.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Define the relationship between OrderDetail and Package
     * Each order detail can optionally be linked to one package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
