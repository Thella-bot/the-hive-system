<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammeWaitlist extends Model
{
    protected $fillable = [
        'programme_id', 'user_id', 'position', 'joined_at', 'notified_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Scopes ---

    public function scopeForProgramme($query, int $programmeId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('programme_id', $programmeId);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNotified($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('notified_at');
    }

    public function scopePending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('notified_at');
    }
}
