<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Key extends Model
{
    protected $fillable = [
        'label', 'location', 'status',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(KeyAssignment::class);
    }

    public function currentAssignment(): HasMany
    {
        return $this->hasMany(KeyAssignment::class)->whereNull('returned_at');
    }

    // --- Scopes ---

    public function scopeAvailable($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeIssued($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'issued');
    }

    public function scopeByLocation($query, string $location): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('location', $location);
    }
}

class KeyAssignment extends Model
{
    protected $fillable = [
        'key_id', 'user_id', 'issued_at', 'returned_at', 'status',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Scopes ---

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('returned_at');
    }

    public function scopeReturned($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('returned_at');
    }

    public function scopeForKey($query, int $keyId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('key_id', $keyId);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }
}
