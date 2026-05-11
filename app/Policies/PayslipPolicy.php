<?php namespace App\Policies;
use App\Models\Payslip;
use App\Models\User;

class PayslipPolicy
{
    public function viewAny(User $user) { return $user->hasAnyRole(['hr_staff','admin']) || $user->hasAnyRole(['instructor','staff']); }
    public function view(User $user, Payslip $payslip) {
        return $user->id === $payslip->user_id || $user->hasAnyRole(['hr_staff', 'admin']);
    }
    public function create(User $user) { return $user->hasAnyRole(['hr_staff', 'admin']); }
}
