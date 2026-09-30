<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's request to join (enrollment) or leave (deregistration) a module.
 *
 * Requests are pending until an authorised staff member approves or rejects
 * them. Approving an enrollment request creates the real Enrollment row;
 * approving a deregistration request removes it.
 */
class EnrollmentRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const TYPE_ENROLLMENT = 'enrollment';
    public const TYPE_DEREGISTRATION = 'deregistration';

    protected $fillable = [
        'user_id',
        'module_id',
        'type',
        'academic_year',
        'semester',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isEnrollment(): bool
    {
        return $this->type === self::TYPE_ENROLLMENT;
    }

    public function isDeregistration(): bool
    {
        return $this->type === self::TYPE_DEREGISTRATION;
    }
}
