<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EnrollmentRequest;
use App\Models\User;

class EnrollmentRequestPolicy extends BasePolicy
{
    /**
     * Any staff member who may view enrollments can review requests.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-enrollments');
    }

    /**
     * Reviewers see every request; a student sees only their own.
     */
    public function view(User $user, EnrollmentRequest $enrollmentRequest): bool
    {
        return $user->can('view-enrollments') || $user->id === $enrollmentRequest->user_id;
    }

    /**
     * Only students submit requests. Staff enroll directly via the admin routes.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('student');
    }

    /**
     * Only a reviewer may decide a request, and only while it is still pending.
     */
    public function update(User $user, EnrollmentRequest $enrollmentRequest): bool
    {
        return $user->can('view-enrollments') && $enrollmentRequest->isPending();
    }

    /**
     * A student may withdraw their own pending request; a reviewer may remove any.
     */
    public function delete(User $user, EnrollmentRequest $enrollmentRequest): bool
    {
        if ($user->can('view-enrollments')) {
            return true;
        }

        return $user->id === $enrollmentRequest->user_id && $enrollmentRequest->isPending();
    }
}
