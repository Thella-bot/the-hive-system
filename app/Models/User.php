<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // --- Relationships ---

    public function profile(): MorphTo
    {
        return $this->morphTo();
    }

    public function headOfDepartment(): HasOne
    {
        return $this->hasOne(Department::class, 'head_user_id');
    }

    // --- Helpers ---

    public function isStaff(): bool
    {
        return $this->hasRole(['super-admin', 'school-admin', 'department-head', 'chef-instructor']);
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function getTypeAttribute(): string
    {
        if ($this->isStaff()) return 'staff';
        if ($this->isStudent()) return 'student';
        return 'unknown';
    }
}