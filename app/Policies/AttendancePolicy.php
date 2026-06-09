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
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']);
    }

    public function checkin(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff', 'student']);
    }

    public function update(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }

    public function delete(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }
}
