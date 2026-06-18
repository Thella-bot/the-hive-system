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

    // --- Scopes ---

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('category', $category);
    }

    public function scopeExpiringSoon($query, int $days = 30): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('contract_expiry', '<=', now()->addDays($days))
            ->where('contract_expiry', '>=', now())
            ->where('is_active', true);
    }

    // --- Helpers ---

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