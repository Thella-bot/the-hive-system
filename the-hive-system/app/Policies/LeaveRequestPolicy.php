<?php namespace App\Policies;
use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user) { return true; }
    public function create(User $user) {
        return $user->hasAnyRole(['instructor','staff','hr_staff']); // no students allowed
    }
    public function view(User $user, LeaveRequest $leaveRequest) {
        return $user->id === $leaveRequest->user_id || $user->hasAnyRole(['hr_staff', 'admin']);
    }
    public function update(User $user, LeaveRequest $leaveRequest) {
        return $user->hasRole(['hr_staff','admin']) && $leaveRequest->status === 'pending';
    }
}
