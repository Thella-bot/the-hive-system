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
}
