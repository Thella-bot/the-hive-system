<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bookmark extends Model
{
    protected $fillable = [
        'user_id',
        'note',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookmarkable(): MorphTo
    {
        return $this->morphTo();
    }

    // --- Scopes ---

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }
}
