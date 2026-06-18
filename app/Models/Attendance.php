<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'checked_in_at',
        'method',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    // --- Scopes ---

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEvent($query, int $eventId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeCheckedIn($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('checked_in_at');
    }

    public function scopeRecent($query, int $days = 7): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByMethod($query, string $method): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('method', $method);
    }
}
