<?php

namespace App\Models;

use App\Enums\GradableType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gradable extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'module_id',
        'instructor_id',
        'title',
        'description',
        'due_date',
        'duration_minutes',
        'max_file_size',
        'allowed_types',
        'max_marks',
        'weight',
    ];

    protected $casts = [
        'type' => GradableType::class,
        'due_date' => 'datetime',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}