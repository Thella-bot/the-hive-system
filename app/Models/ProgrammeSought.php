<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgrammeSought extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'programme_id',
        'status',
        'notes',
    ];

    /**
     * Get the programme that the applicant is seeking.
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
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

    public function scopeForProgramme($query, int $programmeId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('programme_id', $programmeId);
    }
}
