<?php

namespace App\Policies;

use App\Models\Placement;
use App\Models\User;

class PlacementPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'career-services']);
    }

    public function view(User $user, Placement $placement): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'career-services']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'career-services']);
    }

    public function update(User $user, Placement $placement): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'career-services']);
    }

    public function delete(User $user, Placement $placement): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}
