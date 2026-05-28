<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Programme extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration',
        'total_price',
        'monthly_fee',
        'registration_fee',
        'academic_resource_fee',
        'uniform_fee',
        'department_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProgrammeVariant::class);
    }

    public function shortCourses(): MorphMany
    {
        return $this->morphMany(ShortCourse::class, 'courseable');
    }

    // Shortcut: default variant (first active one)
    public function getDefaultVariant()
    {
        return $this->variants()->where('is_active', true)->first();
    }
}