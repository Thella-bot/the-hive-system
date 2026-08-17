<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Payslip;
use App\Models\User;

class PayslipPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin', 'it-support', 'academic-director', 'finance', 'hr-manager'
        ]);
    }

    public function view(User $user, Payslip $payslip): bool
    {
        return $user->id === $payslip->user_id || $user->can('view-payslips');
    }

    public function create(User $user): bool
    {
        return $user->can('create-payslips');
    }

    public function update(User $user, Payslip $payslip): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'hr-manager'])
            || $payslip->user_id === $user->id;
    }

    public function delete(User $user, Payslip $payslip): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'hr-manager']);
    }

    public function generateBatch(User $user): bool
    {
        return $user->can('create-payslips');
    }

    public function download(User $user, Payslip $payslip): bool
    {
        return $user->id === $payslip->user_id || $user->can('view-payslips');
    }
}