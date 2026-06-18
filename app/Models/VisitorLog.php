<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    protected $fillable = [
        'visitor_name', 'id_number', 'host_user_id', 'purpose', 'arrived_at', 'departed_at', 'status',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'departed_at' => 'datetime',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    // --- Scopes ---

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('departed_at');
    }

    public function scopeCompleted($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('departed_at');
    }

    public function scopeForHost($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('host_user_id', $userId);
    }

    public function scopeToday($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereDate('arrived_at', now()->toDateString());
    }

    public function scopeRecent($query, int $days = 7): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('arrived_at', '>=', now()->subDays($days));
    }
}
