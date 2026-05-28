<?php namespace App\Policies;
use App\Models\Payslip;
use App\Models\User;

class PayslipPolicy extends BasePolicy
{
    public function viewAny(User $user) {
        // Admins and all staff can view payslips
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff', 'department-head', 'chef-instructor']);
    }
    public function view(User $user, Payslip $payslip) {
        return $user->id === $payslip->user_id || $user->can('view-payslips');
    }
    public function create(User $user) {
        return $user->can('create-payslips');
    }
}