<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
    /**
     * Determine if the user can view the student list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }

    /**
     * Determine if the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }

    /**
     * Determine if the user can update a student.
     */
    public function update(User $user, User $student): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }

    /**
     * Determine if the user can delete a student.
     */
    public function delete(User $user, User $student): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }
}
