<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\StudentProgress;
use App\Models\User;

class StudentProgressPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, StudentProgress $progress): bool
    {
        return $user->id === $progress->user_id || $user->isStaff();
    }

    public function update(User $user, Module $module = null): bool
    {
        return $user->isStudent() && $module->students->contains($user);
    }
}
