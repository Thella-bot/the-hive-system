<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year',
        'semester',
        'department_id',
        'expense_category_id',
        'approved_budget',
        'allocated_amount',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'approved_budget' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    // Status: draft, active, closed

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getSpentAmountAttribute(): float
    {
        return $this->expenses()
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');
    }

    public function getAvailableAmountAttribute(): float
    {
        return $this->allocated_amount - $this->spent_amount;
    }

    public function getPercentUsedAttribute(): float
    {
        if ($this->allocated_amount <= 0) return 0;
        return round(($this->spent_amount / $this->allocated_amount) * 100, 2);
    }

    public function getIsOverspentAttribute(): bool
    {
        return $this->spent_amount > $this->allocated_amount;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'active' => 'Active',
            'closed' => 'Closed',
            default => $this->status,
        };
    }
}