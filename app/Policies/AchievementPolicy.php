<?php

namespace App\Policies;

use App\Models\Achievement;
use App\Models\User;

class AchievementPolicy
{
    public function delete(User $user, Achievement $achievement): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }
}
