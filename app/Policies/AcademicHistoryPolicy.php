<?php

namespace App\Policies;

use App\Models\AcademicHistory;
use App\Models\User;

class AcademicHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'registrar']);
    }

    public function view(User $user, AcademicHistory $history): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'registrar']);
    }
}
