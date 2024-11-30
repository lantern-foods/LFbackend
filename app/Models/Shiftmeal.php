<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftMeal extends Model
{
    use HasFactory;

    protected $table = 'shift_meals';

    protected $fillable = [
        'shift_id',
        'meal_id',
        'quantity'
    ];


    public static function booted()
    {
        static::creating(function ($model) {
            $model->meal->update(['express_status' => 1]);
        });
    }

    // Disable timestamps if your table does not have created_at and updated_at columns
    public $timestamps = false;

    /**
     * Define the relationship between ShiftMeal and Shift
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Define the relationship between ShiftMeal and Meal
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
