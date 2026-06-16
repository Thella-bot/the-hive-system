<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, HasRoles, Searchable, TwoFactorAuthenticatable;

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

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function profile(): MorphOne
    {
        return $this->morphOne(Profile::class, 'profileable');
    }

    public function headOfDepartment(): HasOne
    {
        return $this->hasOne(Department::class, 'head_user_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function salaryProfile(): HasOne
    {
        return $this->hasOne(SalaryProfile::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_user');
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function instructedModules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_instructor', 'user_id', 'module_id');
    }

    // --- Helpers ---

    /**
     * Check if user is staff (not student, parent, or alumni)
     */
    public function isStaff(): bool
    {
        return !$this->isStudent() && !$this->isParentGuardian() && !$this->isAlumni();
    }

    /**
     * Check if user has student role
     */
    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is parent/guardian
     */
    public function isParentGuardian(): bool
    {
        return $this->hasRole('parent-guardian');
    }

    /**
     * Check if user is alumni
     */
    public function isAlumni(): bool
    {
        return $this->hasRole('alumni');
    }

    /**
     * Check if user is faculty (instructor role)
     */
    public function isFaculty(): bool
    {
        return $this->hasAnyRole([
            'chef-instructor',
            'pastry-instructor',
            'sous-chef',
            'academic-director',
        ]);
    }

    /**
     * Check if user is a admin or IT support
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'it-support']);
    }

    /**
     * Check if user can manage finances
     */
    public function canAccessFinance(): bool
    {
        return $this->hasAnyRole(['super-admin', 'finance', 'hr-manager']);
    }

    /**
     * Check if user can manage students (admissions, registrar, etc.)
     */
    public function canManageStudents(): bool
    {
        return $this->hasAnyRole([
            'super-admin',
            'academic-director',
            'program-coordinator',
            'admissions-officer',
            'registrar',
            'examination-cell',
        ]);
    }

    /**
     * Check if user can access kitchen operations
     */
    public function canAccessKitchen(): bool
    {
        return $this->hasAnyRole([
            'super-admin',
            'academic-director',
            'chef-instructor',
            'pastry-instructor',
            'sous-chef',
            'procurement-manager',
            'storekeeper',
        ]);
    }

    /**
     * Check if user needs registration completion
     */
    public function needsRegistration(): bool
    {
        return $this->applications()
            ->where('status', 'approved')
            ->whereNotNull('admitted_at')
            ->where(function ($q) {
                $q->where('registration_status', '!=', 'completed')
                    ->orWhereNull('registration_status');
            })
            ->exists();
    }

    /**
     * Get user's primary role name
     */
    public function getPrimaryRole(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * Get user's role display name
     */
    public function getRoleDisplayName(): string
    {
        $role = $this->getPrimaryRole();
        if (!$role) return 'Unknown';

        return \App\Enums\UserRole::tryFrom($role)?->displayName() ?? ucwords(str_replace('-', ' ', $role));
    }

    /**
     * Get all role names as array
     */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(\App\Models\Application::class);
    }

    /**
     * Get user type: staff, student, parent, alumni, or unknown
     */
    public function getTypeAttribute(): string
    {
        if ($this->isStudent()) return 'student';
        if ($this->isParentGuardian()) return 'parent';
        if ($this->isAlumni()) return 'alumni';
        if ($this->isStaff()) return 'staff';
        return 'unknown';
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}