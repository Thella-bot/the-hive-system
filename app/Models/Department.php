<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function staff(): HasMany
    {
        return $this->hasMany(Profile::class)->where('profileable_type', User::class);
    }

    // --- Scopes ---

    public function scopeActive($query)
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