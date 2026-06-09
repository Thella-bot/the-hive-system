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
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']);
    }

    public function view(User $user, ShortCourseApplication $shortCourseApplication): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function review(User $user, ShortCourseApplication $shortCourseApplication): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }
}