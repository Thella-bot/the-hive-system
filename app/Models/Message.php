<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'module_id',
        'chat_channel_id',
        'message',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function chatChannel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChatChannel::class);
    }

    // --- Scopes ---

    public function scopeForChannel($query, int $channelId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('chat_channel_id', $channelId);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 7): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}