<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, LeaveRequest $leaveRequest)
    {
        return $user->hasAnyRole(['hr_staff', 'admin']) || $user->id === $leaveRequest->user_id;
    }

    public function create(User $user)
    {
        return true; // All authenticated users can create leave requests
    }

    public function update(User $user, LeaveRequest $leaveRequest)
    {
        return $user->hasAnyRole(['hr_staff', 'admin']);
    }

    public function delete(User $user, LeaveRequest $leaveRequest)
    {
        return $user->hasAnyRole(['hr_staff', 'admin']);
    }
}
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