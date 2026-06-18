<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionNote extends Model
{
    protected $fillable = [
        'user_id',
        'module_id',
        'title',
        'content',
        'type',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    // --- Scopes ---

    public function scopeForModule($query, int $moduleId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('module_id', $moduleId);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, string $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', $type);
    }
}