<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\ConvectionaryIncome;
use App\Models\User;

class ConvectionaryIncomePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function view(User $user, ConvectionaryIncome $income): bool
    {
        if ($user->hasAnyRole(['super-admin', 'it-support'])) {
            return true;
        }

        return $user->hasRole('finance') && $income->recorded_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance']);
    }

    public function update(User $user, ConvectionaryIncome $income): bool
    {
        if ($user->hasAnyRole(['super-admin', 'it-support'])) {
            return true;
        }

        return $user->hasRole('finance') && $income->recorded_by === $user->id;
    }

    public function delete(User $user, ConvectionaryIncome $income): bool
    {
        return $this->update($user, $income);
    }
}