<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortCourseApplication extends Model
{
    protected $fillable = [
        'user_id',
        'short_course_id',
        'name',
        'email',
        'phone',
        'notes',
        'status',
        'applied_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shortCourse(): BelongsTo
    {
        return $this->belongsTo(ShortCourse::class);
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

    public function scopeForShortCourse($query, int $shortCourseId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('short_course_id', $shortCourseId);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 7): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('applied_at', '>=', now()->subDays($days));
    }
}
