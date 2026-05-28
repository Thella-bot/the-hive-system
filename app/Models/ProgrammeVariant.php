<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammeVariant extends Model
{
    protected $fillable = [
        'programme_id',
        'label',
        'duration',
        'total_price',
        'monthly_fee',
        'is_active',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
}