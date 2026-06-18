<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'user_id',
        'vendor_id',
        'budget_id',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'status',
        'approved_by',
        'approved_at',
        'receipt_path',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'date',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    // Status: pending, approved, rejected, paid, cancelled
    // Payment Method: cash, bank_transfer, mobile_money, cheque

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getIsApprovedAttribute(): bool
    {
        return in_array($this->status, ['approved', 'paid']);
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => strtoupper($this->status ?? 'unknown'),
        };
    }

    public static function generateExpenseNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $last = static::where('expense_number', 'like', "EXP-{$year}{$month}%")
            ->orderByDesc('expense_number')
            ->first();

        $sequence = $last ? (int) substr($last->expense_number, -4) + 1 : 1;

        return sprintf('EXP-%s%s-%04d', $year, $month, $sequence);
    }

    public static function boot(): void
    {
        static::creating(function (Expense $expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = static::generateExpenseNumber();
            }
            if (empty($expense->expense_date)) {
                $expense->expense_date = now();
            }
        });

        parent::boot();
    }

    // --- Scopes ---

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'paid']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForBudget($query, int $budgetId)
    {
        return $query->where('budget_id', $budgetId);
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('expense_date', '>=', now()->subDays($days));
    }
}