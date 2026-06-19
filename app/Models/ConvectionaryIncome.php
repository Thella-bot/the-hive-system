<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ConvectionaryIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'source',
        'amount',
        'income_date',
        'description',
        'payment_method',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'received',
    ];

    public const SOURCES = [
        'canteen' => 'Canteen',
        'events' => 'Events',
        'fundraising' => 'Fundraising',
        'donations' => 'Donations',
        'rentals' => 'Rentals',
        'other' => 'Other',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'mobile_money' => 'Mobile Money',
        'card' => 'Card',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($income) {
            if (empty($income->reference)) {
                $income->reference = 'CI-' . date('Y') . '-' . strtoupper(uniqid());
            }
        });
    }

    public static function getTotalByAcademicYear($year)
    {
        return self::where('status', 'received')
            ->whereYear('income_date', $year)
            ->sum('amount');
    }

    public static function getTotalBySource($source, $year = null)
    {
        $query = self::where('source', $source)->where('status', 'received');
        if ($year) {
            $query->whereYear('income_date', $year);
        }
        return $query->sum('amount');
    }
}