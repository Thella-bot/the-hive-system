<?php

namespace App\Policies;

use App\Models\DisciplinaryAction;
use App\Models\User;

class DisciplinaryActionPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'registrar', 'hr-manager']);
    }

    public function view(User $user, DisciplinaryAction $disciplinary): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'registrar', 'hr-manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'registrar', 'hr-manager']);
    }

    public function update(User $user, DisciplinaryAction $disciplinary): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'registrar', 'hr-manager']);
    }

    public function delete(User $user, DisciplinaryAction $disciplinary): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}
