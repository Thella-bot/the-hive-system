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
        return $user->isStaff();
    }

    public function view(User $user, ShortCourse $shortCourse): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'admissions-officer', 'events-pr-manager']);
    }

    public function update(User $user, ShortCourse $shortCourse): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'admissions-officer', 'events-pr-manager']);
    }

    public function delete(User $user, ShortCourse $shortCourse): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']);
    }
}