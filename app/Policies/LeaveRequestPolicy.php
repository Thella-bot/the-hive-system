<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        // HR/Admin can view any request, users can only view their own.
        return $user->hasAnyRole(['hr_staff', 'admin']) || $user->id === $leaveRequest->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All staff can create leave requests, but http://127.0.0.1:8000/loginstudents cannot.
        return $user->hasAnyRole(['instructor', 'staff', 'hr_staff', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // HR/Admin can only update requests that are still pending.
        return $user->hasAnyRole(['hr_staff', 'admin']) && $leaveRequest->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasAnyRole(['hr_staff', 'admin']);
    }
}