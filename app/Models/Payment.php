<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'payment_reference',
        'amount',
        'payment_method',
        'payment_date',
        'recorded_by',
        'status',
        'notes',
        'proof_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'recorded_at' => 'date',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    // Methods: cash, bank_transfer, mobile_money, card, other
    // Status: pending, completed, failed, refunded

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return in_array($this->status, ['completed', 'refunded']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Verification',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            default => strtoupper($this->status ?? 'unknown'),
        };
    }

    public static function generateReference(): string
    {
        $year = date('Y');
        $last = static::where('payment_reference', 'like', "PAY-{$year}%")
            ->orderByDesc('payment_reference')
            ->first();

        $sequence = $last
            ? (int) substr($last->payment_reference, -4) + 1
            : 1;

        return sprintf('PAY-%s-%04d', $year, $sequence);
    }

    public static function boot(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->payment_reference)) {
                $payment->payment_reference = static::generateReference();
            }
            if (empty($payment->recorded_by) && auth()->check()) {
                $payment->recorded_by = auth()->id();
            }
        });

        parent::boot();
    }

    // --- Scopes ---

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForInvoice($query, int $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('payment_date', '>=', now()->subDays($days));
    }
}