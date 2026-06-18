<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pay_period_start',
        'pay_period_end',
        'gross_salary',
        'deductions',
        'net_salary',
        'earnings',
        'deductions_breakdown',
        'leave_deducted',
        'leave_days_taken',
        'leave_taken_detail',
    ];

    protected $casts = [
        'earnings' => 'array',
        'deductions_breakdown' => 'array',
        'leave_taken_detail' => 'array',
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'gross_salary' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'leave_deducted' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Scopes ---

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPeriod($query, string $startDate, string $endDate): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('pay_period_start', '>=', $startDate)
            ->where('pay_period_end', '<=', $endDate);
    }

    public function scopeRecent($query, int $months = 6): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('pay_period_start', '>=', now()->subMonths($months));
    }
}