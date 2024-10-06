<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'customer_addresses';  // Table name in your database

    protected $fillable = [
        'client_id',
        'address_name',
        'location_name',
        'location_status',
    ];

    /**
     * Define the relationship between CustomerAddress and Client
     * Each customer address belongs to one client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Optionally disable timestamps if your table doesn't have `created_at` and `updated_at`.
     * Laravel assumes timestamps by default.
     */
    public $timestamps = true;  // Set to false if your table doesn't have timestamps
}
