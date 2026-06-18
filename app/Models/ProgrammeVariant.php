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

    // --- Scopes ---

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForProgramme($query, int $programmeId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('programme_id', $programmeId);
    }
}