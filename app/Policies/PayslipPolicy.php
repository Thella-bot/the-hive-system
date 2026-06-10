<?php namespace App\Policies;

use App\Models\Payslip;
use App\Models\User;

class PayslipPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff', 'department-head', 'chef-instructor']);
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
        return $user->can('edit-payslips') || $payslip->user_id === $user->id;
    }

    public function delete(User $user, Payslip $payslip): bool
    {
        return $user->can('delete-payslips');
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