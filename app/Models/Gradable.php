<?php

namespace App\Models;

use App\Enums\GradableType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gradable extends Model
{
    use HasFactory;

    public const SUBMISSION_TYPE_FILE_UPLOAD = 'file_upload';
    public const SUBMISSION_TYPE_ONLINE_FILLABLE = 'online_fillable';
    public const SUBMISSION_TYPE_ONLINE_MCQ = 'online_multiple_choice';

    protected $fillable = [
        'type',
        'submission_type',
        'module_id',
        'instructor_id',
        'title',
        'description',
        'due_date',
        'duration_minutes',
        'time_limit_minutes',
        'max_attempts',
        'show_correct_answers',
        'shuffle_questions',
        'shuffle_options',
        'max_file_size',
        'allowed_types',
        'max_marks',
        'weight',
    ];

    protected $casts = [
        'type' => GradableType::class,
        'due_date' => 'datetime',
        'show_correct_answers' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(GradableAttachment::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(GradableQuestion::class)->orderBy('sort_order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GradableAnswer::class);
    }

    public function isOnlineAssessment(): bool
    {
        return in_array($this->submission_type, [
            self::SUBMISSION_TYPE_ONLINE_FILLABLE,
            self::SUBMISSION_TYPE_ONLINE_MCQ,
        ]);
    }

    public function isMcqAssessment(): bool
    {
        return $this->submission_type === self::SUBMISSION_TYPE_ONLINE_MCQ;
    }

    public function isFileUpload(): bool
    {
        return $this->submission_type === self::SUBMISSION_TYPE_FILE_UPLOAD || empty($this->submission_type);
    }

    // --- Scopes ---

    public function scopeForModule($query, int $moduleId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('module_id', $moduleId);
    }

    public function scopeForInstructor($query, int $instructorId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeDueSoon($query, int $days = 3): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>=', now());
    }

    public function scopeOverdue($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('due_date', '<', now());
    }

    public function scopeActiveType($query, string $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', $type);
    }
}