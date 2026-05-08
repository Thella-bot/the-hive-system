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

    // Calculate number of days (excluding weekends? keep simple)
    public function days()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}