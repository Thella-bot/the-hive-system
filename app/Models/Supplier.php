<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'category',
        'contact_name',
        'phone',
        'email',
        'contract_expiry',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'contract_expiry' => 'date',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // Categories: food, equipment, supplies, maintenance, services, other

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->contract_expiry !== null && $this->contract_expiry->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->is_expired) {
            return 'Contract Expired';
        }

        return 'Active';
    }
}