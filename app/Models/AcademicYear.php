<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_current' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Only one academic year can be "current" at a time
        static::saving(function (AcademicYear $year) {
            if ($year->is_current) {
                static::query()
                    ->where('id', '!=', $year->id)
                    ->update(['is_current' => false]);
            }
        });
    }

    // --- Relationships ---

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }

    // --- Helpers ---

    public function getIsOngoingAttribute(): bool
    {
        return now()->between($this->start_date, $this->end_date);
    }

    /**
     * Generate default cohorts (January, April, August) for each
     * department except Administration.
     */
    public function generateDefaultCohorts(): int
    {
        $year = $this->start_date->year;

        $departments = \App\Models\Department::academic()
            ->where('is_active', true)
            ->get();

        $created = 0;

        foreach (Cohort::DEFAULT_COHORTS as $cohortDef) {
            $startDate = now()->setDate($year, $cohortDef['month'], 1)->startOfDay();
            $endDate = now()->setDate($year, $cohortDef['end_month'], $cohortDef['end_day'])->endOfDay();

            foreach ($departments as $department) {
                $existing = Cohort::where('academic_year_id', $this->id)
                    ->where('department_id', $department->id)
                    ->where('name', $cohortDef['month_name'] . ' ' . $year)
                    ->exists();

                if ($existing) {
                    continue;
                }

                $isActive = $startDate <= now() && $endDate >= now();

                Cohort::create([
                    'name'           => $cohortDef['month_name'] . ' ' . $year,
                    'department_id'  => $department->id,
                    'academic_year_id' => $this->id,
                    'max_students'   => 20,
                    'is_active'      => $isActive,
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                ]);

                $created++;
            }
        }

        return $created;
    }

    // --- Scopes ---

    public function scopeCurrent($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeOngoing($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }
}
