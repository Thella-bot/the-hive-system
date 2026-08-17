<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;

class RoleService
{
    private const ADMIN_ROLES = ['super-admin', 'it-support'];

    private const FACULTY_ROLES = [
        'chef-instructor',
        'pastry-instructor',
        'sous-chef',
        'academic-director',
    ];

    private const NON_ACADEMIC_STAFF_ROLES = [
        'finance',
        'hr-manager',
        'procurement-manager',
        'storekeeper',
        'librarian',
        'career-services',
        'events-pr-manager',
        'cafeteria-manager',
        'admissions-officer',
        'registrar',
        'examination-cell',
        'program-coordinator',
    ];

    public function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(self::ADMIN_ROLES);
    }

    public function isFaculty(User $user): bool
    {
        return $user->hasAnyRole(self::FACULTY_ROLES);
    }

    public function isStaff(User $user): bool
    {
        return $user->isStaff();
    }

    public function isStudent(User $user): bool
    {
        return $user->hasRole('student');
    }

    public function isNonAcademicStaff(User $user): bool
    {
        return $user->hasAnyRole(self::NON_ACADEMIC_STAFF_ROLES) && !$this->isFaculty($user);
    }

    public function canAccessFinance(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'finance', 'hr-manager']);
    }

    public function canManageStudents(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'academic-director',
            'program-coordinator',
            'admissions-officer',
            'registrar',
            'examination-cell',
        ]);
    }

    public function canAccessKitchen(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'academic-director',
            'chef-instructor',
            'pastry-instructor',
            'sous-chef',
            'procurement-manager',
            'storekeeper',
        ]);
    }

    public function canAccessDashboard(User $user): bool
    {
        return $this->isAdmin($user) || $this->isFaculty($user) || $this->isNonAcademicStaff($user) || $this->isStudent($user);
    }

    public function getDashboardDataType(User $user): ?string
    {
        if ($this->isAdmin($user)) {
            return 'admin';
        }

        if ($this->isFaculty($user)) {
            return 'instructor';
        }

        if ($this->isNonAcademicStaff($user)) {
            return 'non_academic_staff';
        }

        if ($this->isStudent($user)) {
            return 'student';
        }

        return null;
    }
}