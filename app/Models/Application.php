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
    ];

    // --- Admission ---

    public function isAdmitted(): bool
    {
        return $this->status === 'approved' && $this->admitted_at !== null;
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
}