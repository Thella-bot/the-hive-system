<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShortCourse extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'duration',
        'price',
        'start_date',
        'end_date',
        'is_active',
        'accepting_applications',
        'courseable_id',
        'courseable_type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'accepting_applications' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function courseable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeOpen($q)
    {
        return $q->where('accepting_applications', true);
    }
}