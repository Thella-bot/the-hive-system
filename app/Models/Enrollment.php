<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'module_id', 'academic_year', 'semester'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    // --- Scopes ---

    public function scopeForStudent($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForModule($query, int $moduleId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('module_id', $moduleId);
    }

    public function scopeForAcademicYear($query, string $year): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('academic_year', $year);
    }

    public function scopeForSemester($query, int $semester): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('semester', $semester);
    }
}