<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatChannel extends Model
{
    protected $fillable = [
        'name',
        'channel_type',
        'channel_id',
        'participants',
    ];

    protected $casts = [
        'participants' => 'array',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'channel_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'channel_id');
    }

    public function scopeGeneral($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('channel_type', 'general');
    }

    public function scopeDepartment($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('channel_type', 'department');
    }

    public function scopeDirect($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('channel_type', 'direct');
    }

    public function scopeForUser($query, $user): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('channel_type', 'general')
              ->orWhere('channel_type', 'department')
              ->orWhereJsonContains('participants', (string) $user->id);
        });
    }
}