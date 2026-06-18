<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGrade extends Model
{
    protected $fillable = ['gradable_id', 'student_id', 'marks', 'comments'];

    protected $casts = [
        'marks' => 'decimal:2',
    ];

    public function gradable(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Gradable::class);
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
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

    public function scopePassed($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('marks', '>=', 50);
    }

    public function scopeFailed($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('marks', '<', 50);
    }
}