<?php

namespace App\Policies;

use App\Models\Gradable;
use App\Models\User;

class GradablePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Gradable $gradable): bool
    {
        if ($user->hasAnyRole(['super-admin', 'school-admin'])) {
            return true;
        }

        if ($user->hasRole('academic_staff') && $gradable->instructor_id === $user->id) {
            return true;
        }

        if ($user->hasRole('student')) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff']);
    }

    public function update(User $user, Gradable $gradable): bool
    {
        if ($user->hasAnyRole(['super-admin', 'school-admin'])) {
            return true;
        }

        return $user->hasRole('academic_staff') && $gradable->instructor_id === $user->id;
    }

    public function delete(User $user, Gradable $gradable): bool
    {
        if ($user->hasAnyRole(['super-admin', 'school-admin'])) {
            return true;
        }

        return $user->hasRole('academic_staff') && $gradable->instructor_id === $user->id;
    }
}