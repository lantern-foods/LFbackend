<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Markup extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_type',
        'start_date',
        'end_date',
        'mark_up',  // Keep this consistent with your database field name
        'status',
    ];

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
