<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = ['name', 'code', 'description', 'credits', 'department_id'];

    public function programmes(): BelongsToMany
    {
        return $this->belongsToMany(Programme::class, 'programme_module')
            ->withPivot('order_column')
            ->withTimestamps();
    }

    // Kept for backward compat — first programme linked via pivot
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * The students that are enrolled in the module.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot('academic_year', 'semester')
                    ->withTimestamps();
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'module_instructor');
    }

    public function gradables(): HasMany
    {
        return $this->hasMany(Gradable::class);
    }

    // --- Scopes ---

    public function scopeForDepartment($query, int $departmentId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }
}