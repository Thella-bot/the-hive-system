<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Cohort;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CohortPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'program-coordinator', 'academic-director']);
    }

    public function view(User $user, Cohort $cohort): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'program-coordinator', 'academic-director']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'program-coordinator', 'academic-director']);
    }

    public function update(User $user, Cohort $cohort): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'program-coordinator', 'academic-director']);
    }

    public function delete(User $user, Cohort $cohort): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }

    public function restore(User $user, Cohort $cohort): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }

    public function forceDelete(User $user, Cohort $cohort): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}