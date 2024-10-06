<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookApprovalStatus extends Model
{
    use HasFactory;

    protected $table = 'cook_approval_statuses';

    protected $fillable = [
        'kitchen_name_approved',
        'id_number_approved',
        'mpesa_number_approved',
        'health_number_approved',
        'health_expiry_date_approved',
        'shrt_desc_approved',
        'id_front_approved',
        'id_back_approved',
        'health_cert_approved',
        'profile_pic_approved',
        'approved',
        'rejection_reason',
    ];

    protected $casts = [
        'kitchen_name_approved' => 'boolean',
        'id_number_approved' => 'boolean',
        'mpesa_number_approved' => 'boolean',
        'health_number_approved' => 'boolean',
        'health_expiry_date_approved' => 'boolean',
        'shrt_desc_approved' => 'boolean',
        'id_front_approved' => 'boolean',
        'id_back_approved' => 'boolean',
        'health_cert_approved' => 'boolean',
        'profile_pic_approved' => 'boolean',
        'approved' => 'boolean',
    ];
}
