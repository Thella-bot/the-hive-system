<?php

namespace App\Policies;

use App\Models\User;

class StudentAdvancementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'registrar']);
    }

    public function view(User $user, User $student): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'registrar']);
    }

    public function promote(User $user, User $student): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director']);
    }

    public function promoteAll(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'academic-director']);
    }
}
