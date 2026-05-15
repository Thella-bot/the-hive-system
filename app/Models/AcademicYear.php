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

    // --- Scopes ---

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    // --- Helpers ---

    public function getIsOngoingAttribute(): bool
    {
        return now()->between($this->start_date, $this->end_date);
    }
}
