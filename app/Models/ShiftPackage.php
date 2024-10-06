<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftPackage extends Model
{
    use HasFactory;

    protected $table = 'shift_packages';

    protected $fillable = [
        'shift_id',
        'package_id',
        'quantity',
        'package_status',
    ];

    // Disable timestamps if your table does not have created_at and updated_at columns
    public $timestamps = false;

    /**
     * Define the relationship between ShiftPackage and Shift
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Define the relationship between ShiftPackage and Package
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
