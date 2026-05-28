<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryProfile extends Model
{
    protected $fillable = [
        'user_id',
        'base_salary',
        'housing_allowance',
        'transport_allowance',
        'medical_allowance',
        'bonus_rate',
        'tax_rate',
        'pension_rate',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'bonus_rate' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'pension_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getGrossPackageAttribute(): float
    {
        return (float) $this->base_salary
            + (float) $this->housing_allowance
            + (float) $this->transport_allowance
            + (float) $this->medical_allowance;
    }
}