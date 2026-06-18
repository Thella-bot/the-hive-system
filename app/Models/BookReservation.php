<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BookReservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'book_id',
        'reserved_at',
        'expires_at',
        'fulfilled_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'reserved_at' => 'date',
        'expires_at' => 'date',
        'fulfilled_at' => 'date',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->expires_at->isPast();
    }

    public function canFulfill(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '>=', now());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForBook($query, string $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    public function scopeExpiringSoon($query, int $days = 3)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }
}