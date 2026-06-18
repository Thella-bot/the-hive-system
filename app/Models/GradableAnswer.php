<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradableAnswer extends Model
{
    protected $fillable = [
        'gradable_id',
        'student_id',
        'gradable_question_id',
        'gradable_question_option_id',
        'answer_text',
        'is_correct',
        'points_awarded',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_awarded' => 'integer',
    ];

    public function gradable(): BelongsTo
    {
        return $this->belongsTo(Gradable::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(GradableQuestion::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(GradableQuestionOption::class, 'gradable_question_option_id');
    }

    // --- Scopes ---

    public function scopeForStudent($query, int $studentId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForGradable($query, int $gradableId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('gradable_id', $gradableId);
    }

    public function scopeCorrect($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_correct', true);
    }
}