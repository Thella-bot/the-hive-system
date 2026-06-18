<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradableQuestionOption extends Model
{
    protected $fillable = [
        'gradable_question_id',
        'option_text',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(GradableQuestion::class);
    }

    // --- Scopes ---

    public function scopeForQuestion($query, int $questionId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('gradable_question_id', $questionId);
    }

    public function scopeCorrect($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_correct', true);
    }
}