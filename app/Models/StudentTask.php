<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTask extends Model
{
    protected $fillable = [
        'user_id',
        'gradable_id',
        'title',
        'description',
        'due_date',
        'priority',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gradable(): BelongsTo
    {
        return $this->belongsTo(Gradable::class);
    }
}
