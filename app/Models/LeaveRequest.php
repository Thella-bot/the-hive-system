<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = ['user_id', 'type', 'start_date', 'end_date', 'reason', 'status', 'approved_by', 'approved_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    public function days(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
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
            $profile = $this->user->profile;
            if ($profile) {
                $profile->decrement('leave_balance', min($this->days(), $profile->leave_balance));
            }
        }
    }
}