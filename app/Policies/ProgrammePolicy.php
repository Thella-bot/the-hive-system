<?php

namespace App\Policies;

use App\Models\Programme;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgrammePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Programme $programme): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']);
    }

    public function update(User $user, Programme $programme): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator']);
    }

    public function delete(User $user, Programme $programme): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}