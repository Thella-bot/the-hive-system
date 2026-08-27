<?php

namespace App\Policies;

use App\Models\CourseMaterial;
use App\Models\Module;
use App\Models\User;

class CourseMaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CourseMaterial $material, Module $module = null): bool
    {
        if ($material->is_published) {
            return true;
        }
        return $user->isStaff() && ($user->isAdmin() || $material->uploaded_by === $user->id);
    }

    public function create(User $user, Module $module = null): bool
    {
        return $user->isStaff() && ($user->isAdmin() || $module->instructors->contains($user));
    }

    public function update(User $user, CourseMaterial $material, Module $module = null): bool
    {
        return $user->isStaff() && ($user->isAdmin() || $material->uploaded_by === $user->id);
    }

    public function delete(User $user, CourseMaterial $material, Module $module = null): bool
    {
        return $user->isStaff() && ($user->isAdmin() || $material->uploaded_by === $user->id);
    }
}
