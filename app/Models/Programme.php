<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Programme extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration',
        'duration_months',
        'total_price',
        'monthly_fee',
        'registration_fee',
        'academic_resource_fee',
        'uniform_fee',
        'tools_cost',
        'requirements',
        'payment_method',
        'intake_period',
        'career_opportunities',
        'department_id',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'registration_fee' => 'decimal:2',
        'academic_resource_fee' => 'decimal:2',
        'uniform_fee' => 'decimal:2',
        'tools_cost' => 'decimal:2',
        'duration_months' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'programme_module')
            ->withPivot('order_column')
            ->withTimestamps()
            ->orderByPivot('order_column');
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