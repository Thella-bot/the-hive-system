<?php

namespace App\Policies;

use App\Models\ShortCourseApplication;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShortCourseApplicationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, ShortCourseApplication $shortCourseApplication): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function review(User $user, ShortCourseApplication $shortCourseApplication): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'admissions-officer']);
    }
}