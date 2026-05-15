<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Cohort extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'department_id',
        'academic_year_id',
        'max_students',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'max_students' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cohort $cohort) {
            if (empty($cohort->slug)) {
                $cohort->slug = Str::slug($cohort->name);
            }
        });
    }

    // --- Relationships ---

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Profile::class)->where('profileable_type', User::class);
    }

    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // --- Helpers ---

    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->max_students - $this->student_count);
    }

    public function isFull(): bool
    {
        return $this->available_spots === 0;
    }
}