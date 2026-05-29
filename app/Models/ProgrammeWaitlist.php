<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammeWaitlist extends Model
{
    protected $fillable = [
        'programme_id', 'user_id', 'position', 'joined_at', 'notified_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
