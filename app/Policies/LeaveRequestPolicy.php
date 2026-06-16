<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy extends BasePolicy
{
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
        // HR can view any request, users can only view their own.
        return $user->can('edit-leave-requests') || $user->id === $leaveRequest->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All staff can create leave requests, students cannot
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // HR can only update requests that are still pending.
        return $user->can('edit-leave-requests') && $leaveRequest->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('delete-leave-requests');
    }
}