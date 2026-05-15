<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['admin', 'instructor', 'student']);
    }

    public function view(User $user, Assignment $assignment)
    {
        if ($user->hasAnyRole(['admin', 'instructor'])) {
            return true;
        }

        return $user->modules()->where('module_id', $assignment->module_id)->exists();
    }

    public function create(User $user)
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function update(User $user, Assignment $assignment)
    {
        return $user->id === $assignment->instructor_id || $user->hasRole('admin');
    }

    public function delete(User $user, Assignment $assignment)
    {
        return $user->id === $assignment->instructor_id || $user->hasRole('admin');
    }
}