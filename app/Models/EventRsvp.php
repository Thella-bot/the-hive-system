<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRsvp extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'status',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Scopes ---

    public function scopeForEvent($query, int $eventId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAccepted($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'accepted');
    }

    public function scopeDeclined($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'declined');
    }

    public function scopePending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'pending');
    }
}