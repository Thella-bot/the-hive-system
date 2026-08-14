<?php

namespace App\Policies;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AchievementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'program-coordinator', 'career-services', 'events-pr-manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'program-coordinator', 'career-services']);
    }

    public function delete(User $user, Achievement $achievement): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}
