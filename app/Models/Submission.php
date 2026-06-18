<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $table = 'submissions';
    protected $fillable = ['gradable_id', 'student_id', 'file_path', 'submitted_at', 'is_late', 'grade', 'feedback', 'graded_at', 'graded_by'];
    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
        'grade' => 'decimal:2'
    ];

    public function gradable(): BelongsTo
    {
        return $this->belongsTo(Gradable::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
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

    public function scopeGraded($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('grade');
    }

    public function scopePending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('grade');
    }

    public function scopeLate($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_late', true);
    }
}