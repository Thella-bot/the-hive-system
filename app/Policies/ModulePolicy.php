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
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff']);
    }

    public function update(User $user, Module $module): bool
    {
        if ($user->hasAnyRole(['super-admin', 'school-admin'])) {
            return true;
        }

        return $user->hasRole('academic_staff') && $module->instructors()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Module $module): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }
}