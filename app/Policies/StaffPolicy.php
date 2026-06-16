<?php

namespace App\Policies;

use App\Models\User;

class StaffPolicy extends BasePolicy
{
    /**
     * Determine if the user can view the staff list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'hr-manager', 'academic-director']);
    }

    /**
     * Determine if the user can create staff.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'hr-manager']);
    }

    /**
     * Determine if the user can update a staff member.
     */
    public function update(User $user, User $staff): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'hr-manager']);
    }

    /**
     * Determine if the user can delete a staff member.
     */
    public function delete(User $user, User $staff): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}