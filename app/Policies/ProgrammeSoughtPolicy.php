<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\ProgrammeSought;
use App\Models\User;

class ProgrammeSoughtPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
        ]);
    }

    public function view(User $user, ProgrammeSought $application): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
        ]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
        ]);
    }

    public function update(User $user, ProgrammeSought $application): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
        ]);
    }

    public function delete(User $user, ProgrammeSought $application): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}