<?php

namespace App\Policies;

use App\Models\TimetableSlot;
use App\Models\User;

class TimetablePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TimetableSlot $slot): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'registrar']);
    }

    public function update(User $user, TimetableSlot $slot): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'registrar']);
    }

    public function delete(User $user, TimetableSlot $slot): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'academic-director', 'program-coordinator']);
    }
}
