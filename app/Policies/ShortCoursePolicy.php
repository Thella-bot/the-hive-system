<?php

namespace App\Policies;

use App\Models\ShortCourse;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShortCoursePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']);
    }

    public function view(User $user, ShortCourse $shortCourse): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }

    public function update(User $user, ShortCourse $shortCourse): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }

    public function delete(User $user, ShortCourse $shortCourse): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }
}