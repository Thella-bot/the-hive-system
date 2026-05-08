<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'approved_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function enrollments() {
    return $this->hasMany(Enrollment::class);
    }
    
    public function modules() {
        return $this->belongsToMany(Module::class, 'enrollments')
                    ->withPivot('academic_year', 'semester')
                    ->withTimestamps();
    }
    public function instructedModules() {
    return $this->belongsToMany(Module::class, 'module_instructor');
    }
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
}
