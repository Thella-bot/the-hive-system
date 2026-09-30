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

    /**
     * Academic management roles: they own the register, timetables and results
     * but do not teach. They get their own dashboard rather than the generic
     * staff one, which useUser.isAcademicStaff() already assumes.
     */
    private const ACADEMIC_MANAGEMENT_ROLES = [
        'program-coordinator',
        'registrar',
        'examination-cell',
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
    ];

    private const EXTERNAL_ROLES = [
        'parent-guardian',
        'alumni',
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

    public function isAcademicManagement(User $user): bool
    {
        return $user->hasAnyRole(self::ACADEMIC_MANAGEMENT_ROLES);
    }

    public function isParentGuardian(User $user): bool
    {
        return $user->hasRole('parent-guardian');
    }

    public function isAlumni(User $user): bool
    {
        return $user->hasRole('alumni');
    }

    public function isExternal(User $user): bool
    {
        return $user->hasAnyRole(self::EXTERNAL_ROLES);
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
        return $this->getDashboardDataType($user) !== null;
    }

    /**
     * Resolve which dashboard data set a user should receive.
     *
     * The ordering here must stay in step with the mutually exclusive blocks in
     * resources/js/Pages/Hive/Dashboard.vue, otherwise a user gets data that the
     * template never renders.
     */
    public function getDashboardDataType(User $user): ?string
    {
        if ($this->isAdmin($user)) {
            return 'admin';
        }

        if ($this->isFaculty($user)) {
            return 'instructor';
        }

        if ($this->isAcademicManagement($user)) {
            return 'academic_staff';
        }

        if ($this->isNonAcademicStaff($user)) {
            return 'non_academic_staff';
        }

        if ($this->isStudent($user)) {
            return 'student';
        }

        if ($this->isParentGuardian($user)) {
            return 'parent_guardian';
        }

        if ($this->isAlumni($user)) {
            return 'alumni';
        }

        return null;
    }
}