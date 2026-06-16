<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->amount - $this->total_paid;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid'
            && $this->due_date
            && $this->due_date->isPast();
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

        parent::boot();
    }
}