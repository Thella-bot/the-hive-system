<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }
}
