<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftAdminControll extends Model
{
    use HasFactory;

    protected $table = 'shift_admin_controls';

    protected $fillable = [
        'shift_start_time',
        'shift_end_time',
        'all_shifts_closed',
    ];

    // If your table doesn't have `created_at` and `updated_at` columns
    public $timestamps = false;
}
