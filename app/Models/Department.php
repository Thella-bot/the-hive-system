<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'head_user_id',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Department $dept) {
            if (empty($dept->slug)) {
                $dept->slug = Str::slug($dept->name);
            }
        });

        static::updating(function (Department $dept) {
            if ($dept->isDirty('name') && ! $dept->isDirty('slug')) {
                $dept->slug = Str::slug($dept->name);
            }
        });
    }

    // --- Relationships ---

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    public function shortCourses(): MorphMany
    {
        return $this->morphMany(ShortCourse::class, 'courseable');
    }

    public function staff()
    {
        return $this->hasManyThrough(
            User::class,
            Profile::class,
            'department_id', // profiles.department_id -> departments.id
            'id', // users.id = profiles.profileable_id
            'id', // departments.id
            'profileable_id' // profileable_id on profiles
        )
        ->where('profiles.profileable_type', User::class)
        ->whereHas('roles', function ($query) {
            $query->whereIn('name', [
                'super-admin', 'it-support', 'academic-director', 'program-coordinator',
                'chef-instructor', 'pastry-instructor', 'sous-chef',
                'admissions-officer', 'examination-cell', 'registrar', 'finance',
                'procurement-manager', 'storekeeper', 'hr-manager', 'librarian',
                'career-services', 'events-pr-manager', 'cafeteria-manager',
            ]);
        });
    }

    // --- Scopes ---

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    // --- Helpers ---

    public function getActiveCohortCountAttribute(): int
    {
        return $this->cohorts()->where('is_active', true)->count();
    }

    public function getActiveStudentCountAttribute(): int
    {
        return $this->cohorts()
            ->whereHas('students', fn ($q) => $q->where('status', 'active'))
            ->withCount(['students' => fn ($q) => $q->where('status', 'active')])
            ->get()
            ->sum('students_count');
    }
}