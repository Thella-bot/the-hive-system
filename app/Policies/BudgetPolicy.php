<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BudgetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'registrar']);
    }

    public function view(User $user, Budget $budget): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'registrar']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }
}
