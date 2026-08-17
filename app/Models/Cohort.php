<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Represents a student cohort.
 *
 * @package App\Models
 */
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
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'max_students' => 'integer',
            'start_date'   => 'date',
            'end_date'     => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cohort $cohort) {
            if (empty($cohort->slug)) {
                $baseSlug = Str::slug($cohort->name);
                $slug = $baseSlug;
                $counter = 1;
                while (Cohort::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $cohort->slug = $slug;
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

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDepartment($query, int $departmentId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeForAcademicYear($query, int $academicYearId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeNotFull($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw('(SELECT COUNT(*) FROM profiles WHERE cohort_id = cohorts.id AND profileable_type = ?) < cohorts.max_students', [User::class]);
    }

    // --- Helpers ---

    public function getStudentCountAttribute(): int
    {
        // Use direct count query to avoid N+1 when loading multiple cohorts.
        // The students() HasMany already filters by profileable_type = User::class.
        if ($this->relationLoaded('students')) {
            return $this->students->count();
        }

        return (int) \Illuminate\Support\Facades\DB::table('profiles')
            ->where('cohort_id', $this->id)
            ->where('profileable_type', User::class)
            ->count();
    }

    public function getAvailableSpotsAttribute(): int
    {
        return max(0, (int) $this->max_students - (int) $this->student_count);
    }

    public function isFull(): bool
    {
        return $this->available_spots === 0;
    }

    public function isActive(): bool
    {
        if (! $this->start_date || ! $this->end_date) {
            return (bool) $this->is_active;
        }

        return now()->between($this->start_date, $this->end_date) || $this->start_date->isFuture();
    }

    // --- Default cohort definitions ---

    public const DEFAULT_COHORTS = [
        ['month' => 1,  'month_name' => 'January', 'end_month' => 3,  'end_day' => 31],
        ['month' => 4,  'month_name' => 'April',   'end_month' => 7,  'end_day' => 31],
        ['month' => 8,  'month_name' => 'August',  'end_month' => 11, 'end_day' => 30],
    ];
}
