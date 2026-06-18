<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poll extends Model
{
    protected $fillable = [
        'user_id', 'question', 'type', 'options', 'expires_at',
    ];

    protected $casts = [
        'options' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function hasVotedBy(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function voteCounts(): array
    {
        $votes = $this->votes()->get();
        $counts = [];
        foreach ($this->options ?? [] as $opt) {
            $counts[$opt] = $votes->where('choice', $opt)->count();
        }
        $counts['yes'] = $votes->where('choice', 'yes')->count();
        $counts['no'] = $votes->where('choice', 'no')->count();
        return $counts;
    }

    // --- Scopes ---

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('expires_at', '>', now())
            ->orWhereNull('expires_at');
    }

    public function scopeExpired($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }
}
