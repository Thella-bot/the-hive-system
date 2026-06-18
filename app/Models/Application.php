<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'programme_id',
        'variant_id',
        'status',
        'notes',
        'attachments',
        'admitted_at',
        'registration_status',
        'registered_at',
        'payment_proof_path',
        'payment_verified_at',
        'registration_notes',
    ];

    protected $casts = [
        'admitted_at' => 'datetime',
        'registered_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'attachments' => 'array',
    ];

    protected $attributes = [
        'status' => 'pending',
        'registration_status' => 'pending',
    ];

    /**
     * Get attachment URLs with proper URLs for display.
     */
    public function getAttachmentUrls(): array
    {
        if (empty($this->attachments)) {
            return [];
        }

        return collect($this->attachments)->map(function ($attachment) {
            if (isset($attachment['path'])) {
                $attachment['url'] = \Illuminate\Support\Facades\Storage::url($attachment['path']);
            }
            return $attachment;
        })->toArray();
    }

    /**
     * Get attachments by type (e.g., 'certificate', 'employer_letter').
     */
    public function getAttachmentsByType(string $type): array
    {
        return collect($this->attachments)->where('type', $type)->values()->toArray();
    }

    // --- Admission ---

    public function isAdmitted(): bool
    {
        return $this->status === 'approved' && ! empty($this->admitted_at);
    }

    // --- Registration ---

    public function isRegistrationPending(): bool
    {
        return $this->isAdmitted() && $this->registration_status === 'pending';
    }

    public function isRegistrationSubmitted(): bool
    {
        return $this->registration_status === 'submitted';
    }

    public function isRegistrationCompleted(): bool
    {
        return $this->registration_status === 'completed';
    }

    public function isRegistered(): bool
    {
        return $this->isRegistrationCompleted();
    }

    public function needsRegistration(): bool
    {
        return $this->isAdmitted() && ! $this->isRegistered() && ! $this->isRegistrationSubmitted();
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProgrammeVariant::class);
    }

    // --- Scopes ---

    public function scopePending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForProgramme($query, int $programmeId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('programme_id', $programmeId);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRegistrationCompleted($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('registration_status', 'completed');
    }

    public function scopeRegistrationPending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('registration_status', 'pending');
    }

    public function scopeAdmitted($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'approved')->whereNotNull('admitted_at');
    }
}