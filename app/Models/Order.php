<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'client_id',
        'order_no',
        'order_type',
        'dt_req',
        'order_total',
        'status',
        'cook_dely_otp',
        'client_dely_otp',
        'customeraddress_id'
    ];

    /**
     * Define the relationship between Order and Client
     * Each order belongs to one client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Define the relationship between Order and CustomerAddress
     * Each order belongs to one customer address.
     */
    public function customerAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'customeraddress_id');
    }

    /**
     * Define the relationship between Order and OrderDetail
     * Each order can have many order details.
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes that timestamps exist by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
