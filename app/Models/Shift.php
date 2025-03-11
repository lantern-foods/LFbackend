<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'cook_id',
        'start_time',
        'end_time',
        'shift_date',
        'estimated_revenue',
        'shift_status'
    ];

    // Disable timestamps if your table does not have created_at and updated_at columns
    public $timestamps = true;

    /**
     * Define the relationship between Shift and Cook
     */
    public function cook()
    {
        return $this->belongsTo(Cook::class);
    }

    /**
     * Define the relationship between Shift and ShiftMeal
     */
    public function shiftMeals()
    {
        return $this->hasMany(Shiftmeal::class);
    }

    /**
     * Define the relationship between Shift and OrderDetail
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Define the relationship between Shift and Meal
     */
    public function meals() 
    { 
        return $this->belongsToMany(Meal::class, 'shift_meals', 'shift_id', 'meal_id') ->withPivot('quantity'); 
    }

    /**
     * Define the relationship between Shift and Package
     */
    public function packages() 
    { 
        return $this->belongsToMany(Package::class, 'shift_packages', 'shift_id', 'package_id'); 
    }
}
