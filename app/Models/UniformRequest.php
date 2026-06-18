<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformRequest extends Model
{
    protected $fillable = [
        'user_id', 'item_type', 'size', 'quantity', 'status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // --- Scopes ---

    public function scopePending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }
}
