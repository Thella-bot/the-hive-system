<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'date_of_birth',
        'phone', 'address', 'emergency_contact_name',
        'emergency_contact_phone', 'annual_leave_days', 'leave_balance'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
}