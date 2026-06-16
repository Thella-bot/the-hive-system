<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Module $module): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Admin and academic roles can create modules
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
            'chef-instructor',
            'pastry-instructor',
        ]);
    }

    public function update(User $user, Module $module): bool
    {
        // Admin can update all
        if ($user->hasAnyRole(['super-admin', 'it-support', 'academic-director'])) {
            return true;
        }

        // Instructors can update modules they teach
        return $user->hasAnyRole(['chef-instructor', 'pastry-instructor', 'sous-chef'])
            && $module->instructors()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Module $module): bool
    {
        // Only admin can delete modules
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}