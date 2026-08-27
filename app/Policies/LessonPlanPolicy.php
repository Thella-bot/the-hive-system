<?php

namespace App\Policies;

use App\Models\LessonPlan;
use App\Models\Module;
use App\Models\User;

class LessonPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LessonPlan $lessonPlan): bool
    {
        if ($lessonPlan->status === 'published') {
            return true;
        }
        return $user->isStaff() && ($user->isAdmin() || $lessonPlan->created_by === $user->id);
    }

    public function create(User $user, Module $module = null): bool
    {
        return $user->isStaff() && ($user->isAdmin() || $module->instructors->contains($user));
    }

    public function update(User $user, LessonPlan $lessonPlan, Module $module = null): bool
    {
        return $user->isStaff() && ($user->isAdmin() || $lessonPlan->created_by === $user->id);
    }

    public function delete(User $user, LessonPlan $lessonPlan, Module $module = null): bool
    {
        return $user->isStaff() && ($user->isAdmin() || $lessonPlan->created_by === $user->id);
    }
}
