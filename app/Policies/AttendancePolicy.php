<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Attendance $attendance): bool
    {
        // All staff can view attendance
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        // Faculty and admin can create attendance records
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
            'chef-instructor',
            'pastry-instructor',
            'sous-chef',
        ]);
    }

    public function checkin(User $user): bool
    {
        // Staff and students can check in
        return $user->isStaff() || $user->hasRole('student');
    }

    public function update(User $user): bool
    {
        // Admin and program coordinator can update attendance
        return $user->hasAnyRole(['super-admin', 'it-support', 'program-coordinator']);
    }

    public function delete(User $user): bool
    {
        // Only admin can delete attendance
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}
