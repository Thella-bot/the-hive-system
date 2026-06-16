<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcademicYearPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']);
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']);
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}