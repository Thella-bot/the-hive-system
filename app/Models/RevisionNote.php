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
}