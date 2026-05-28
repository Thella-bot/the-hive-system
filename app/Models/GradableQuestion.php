<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradableQuestion extends Model
{
    protected $fillable = [
        'gradable_id',
        'type',
        'question_text',
        'points',
        'sort_order',
        'is_required',
        'explanation',
    ];

    protected $casts = [
        'points' => 'integer',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
    ];

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    public const TYPE_FILL_IN_BLANK = 'fill_in_blank';
    public const TYPE_SHORT_ANSWER = 'short_answer';
    public const TYPE_ESSAY = 'essay';

    public function gradable(): BelongsTo
    {
        return $this->belongsTo(Gradable::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(GradableQuestionOption::class)->orderBy('sort_order');
    }

    public function correctOptions(): HasMany
    {
        return $this->hasMany(GradableQuestionOption::class)->where('is_correct', true);
    }
}