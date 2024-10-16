<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookDocument extends Model
{
    use HasFactory;

    protected $table = 'cooks_documents';  // Ensure this matches your database table name

    protected $fillable = [
        'cook_id',
        'id_front',
        'id_back',
        'health_cert',
        'profile_pic'
    ];

    /**
     * Define the relationship between CookDocument and Cook
     */
    public function cook()
    {
        return $this->belongsTo(Cook::class);
    }
}
