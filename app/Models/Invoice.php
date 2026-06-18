<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Payment;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'programme_id',
        'variant_id',
        'academic_year',
        'semester',
        'type',
        'amount',
        'description',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'issued_at' => 'date',
        'paid_at' => 'date',
    ];

    protected $attributes = [
        'status' => 'pending',
        'type' => 'registration',
    ];

    // Types: registration, tuition, uniform, tools, resource, examination, other
    // Status: pending, partial, paid, overdue, cancelled

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProgrammeVariant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalPaidAttribute(): float
    {
        // Use cached value if loaded, otherwise query
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments
                ->where('status', 'completed')
                ->sum('amount');
        }

        return (float) $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return ($this->amount ?? 0) - $this->total_paid;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'paid' || $this->status === 'cancelled') {
            return false;
        }

        return $this->due_date !== null && $this->due_date->isPast();
    }

    // --- Scopes ---

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopeForAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'partial' => 'Partial Payment',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
            default => $this->status,
        };
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $last = static::where('invoice_number', 'like', "INV-{$year}%")
            ->orderByDesc('invoice_number')
            ->first();

        $sequence = $last
            ? (int) substr($last->invoice_number, -4) + 1
            : 1;

        return sprintf('INV-%s-%04d', $year, $sequence);
    }

    public static function boot(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
            if (empty($invoice->issued_at)) {
                $invoice->issued_at = now();
            }
        });

        // Auto-update status based on payments
        static::updated(function (Invoice $invoice) {
            $invoice->refreshStatus();
        });

        parent::boot();
    }

    /**
     * Auto-update invoice status based on payments
     */
    public function refreshStatus(): void
    {
        $originalStatus = $this->getOriginal('status');

        // If already paid or cancelled, don't change
        if (in_array($originalStatus, ['paid', 'cancelled'])) {
            return;
        }

        $totalPaid = $this->total_paid;
        $amount = $this->amount ?? 0;

        if ($totalPaid >= $amount && $amount > 0) {
            $this->update([
                'status' => 'paid',
                'paid_at' => $this->paid_at ?? now(),
            ]);
        } elseif ($totalPaid > 0 && $totalPaid < $amount) {
            $this->update(['status' => 'partial']);
        } elseif ($this->is_overdue && $originalStatus === 'pending') {
            $this->update(['status' => 'overdue']);
        }
    }

    /**
     * Mark invoice as paid manually
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark invoice as cancelled
     */
    public function markAsCancelled(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Record a payment and auto-update status
     */
    public function recordPayment(array $data): Payment
    {
        $payment = $this->payments()->create($data);

        if ($payment->status === 'completed') {
            $this->refreshStatus();
        }

        return $payment;
    }
}