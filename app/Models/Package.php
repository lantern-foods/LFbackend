<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'cook_id',
        'package_name',
        'package_description',
        'discount',
        'total_price',
    ];

    /**
     * Define the relationship between Package and Cook
     * Each package belongs to one cook.
     */
    public function cook()
    {
        return $this->belongsTo(Cook::class);
    }

    /**
     * Define the relationship between Package and Order (if a package can be part of orders)
     * Uncomment or modify based on application requirements.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Define the relationship between Package and PackageMeal
     * Each package can have many associated package meals.
     */
    public function packageMeals()
    {
        return $this->hasMany(PackageMeal::class);
    }

    /**
     * Define the relationship between Package and Cart
     * Each package can be part of multiple carts.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
