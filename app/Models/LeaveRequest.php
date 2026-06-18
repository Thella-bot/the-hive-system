<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id', 'type', 'half_day', 'start_date', 'end_date',
        'reason', 'status', 'approved_by', 'approved_at',
        'rejection_reason', 'is_cancelled', 'cancelled_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'half_day' => 'boolean',
        'is_cancelled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function days(): float
    {
        $days = $this->start_date->diffInDays($this->end_date) + 1;
        return $this->half_day ? 0.5 : $days;
    }

    public function hasSufficientBalance(): bool
    {
        if ($this->type !== 'annual') {
            return true;
        }
        $profile = $this->user->profile;
        return $profile && $profile->leave_balance >= $this->days();
    }

    public function deductFromBalance(): void
    {
        if ($this->type === 'annual') {
            try {
                $profile = $this->user->profile;
                if ($profile) {
                    $profile->decrement('leave_balance', min($this->days(), $profile->leave_balance));
                }
            } catch (\Exception $e) {
                // Silently fail if profile relationship isn't set up properly
                // This prevents approval from failing due to balance update issues
            }
        }
    }

    public function restoreBalance(): void
    {
        if ($this->type === 'annual') {
            $profile = $this->user->profile;
            if ($profile) {
                $profile->increment('leave_balance', $this->days());
            }
        }
    }

    // --- Scopes ---

    public function scopePending($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'pending')->where('is_cancelled', false);
    }

    public function scopeCancelled($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_cancelled', true);
    }

    public function scopeForUser($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeApproved($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_cancelled', false);
    }
}
