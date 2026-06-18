<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // --- Scopes ---

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode($query, string $code): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('code', $code);
    }
}