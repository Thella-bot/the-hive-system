<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'category', 'contact_name', 'phone', 'email', 'contract_expiry', 'notes',
    ];

    protected $casts = [
        'contract_expiry' => 'date',
    ];
}
