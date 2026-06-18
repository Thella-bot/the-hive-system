<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTask extends Model
{
    protected $fillable = [
        'user_id',
        'gradable_id',
        'title',
        'description',
        'due_date',
        'priority',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gradable(): BelongsTo
    {
        return $this->belongsTo(Gradable::class);
    }

    // --- Scopes ---

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('completed', false);
    }

    public function scopeCompleted($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('completed', true);
    }

    public function scopeDueSoon($query, int $days = 3): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>=', now())
            ->where('completed', false);
    }

    public function scopeOverdue($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('due_date', '<', now())
            ->where('completed', false);
    }

    public function scopeByPriority($query, string $priority): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('priority', $priority);
    }
}
