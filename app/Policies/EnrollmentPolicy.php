<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'registrar',
            'program-coordinator',
            'academic-director',
        ]);
    }
}
