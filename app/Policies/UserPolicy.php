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
        return $user->hasAnyRole(['super-admin', 'it-support', 'hr-manager', 'admissions-officer', 'registrar']);
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'hr-manager', 'admissions-officer']);
    }

    /**
     * Determine if the user can update a user.
     */
    public function update(User $user, User $targetUser): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'hr-manager']);
    }

    /**
     * Determine if the user can delete a user.
     */
    public function delete(User $user, User $targetUser): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}
