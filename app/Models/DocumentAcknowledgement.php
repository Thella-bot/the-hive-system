<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAcknowledgement extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Scopes ---

    public function scopeForDocument($query, int $documentId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('document_id', $documentId);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }
}
