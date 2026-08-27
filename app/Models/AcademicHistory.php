<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'academic_year_id',
        'programme_id',
        'year_level',
        'semester',
        'status',
        'gpa',
        'modules_enrolled',
        'modules_passed',
        'modules_failed',
        'notes',
        'promoted_at',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'year_level' => 'integer',
        'modules_enrolled' => 'integer',
        'modules_passed' => 'integer',
        'modules_failed' => 'integer',
        'promoted_at' => 'datetime',
    ];

    public const STATUS_ENROLLED = 'enrolled';
    public const STATUS_PROMOTED = 'promoted';
    public const STATUS_REPEATED = 'repeated';
    public const STATUS_GRADUATED = 'graduated';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAcademicYear($query, int $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    public function scopePromoted($query)
    {
        return $query->where('status', 'promoted');
    }

    public function scopeGraduated($query)
    {
        return $query->where('status', 'graduated');
    }
}
