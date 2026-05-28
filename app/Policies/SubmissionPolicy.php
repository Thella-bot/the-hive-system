<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use App\Models\Gradable;

class SubmissionPolicy extends BasePolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Submission $submission)
    {
        return $user->id === $submission->student_id || $user->id === $submission->gradable->instructor_id;
    }

    public function create(User $user, Gradable $gradable = null)
    {
        // Called as authorize('create', [Submission::class, $gradable]) from controller
        // Allow if user has student role OR the permission
        if ($user->hasRole('student') || $user->can('create-submissions')) {
            // Check if gradable is open (not overdue)
            if ($gradable && $gradable->due_date && now()->gt($gradable->due_date)) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function update(User $user, Submission $submission)
    {
        return $user->can('grade-submissions') && $user->id === $submission->gradable->instructor_id;
    }

    public function delete(User $user, Submission $submission)
    {
        return $user->id === $submission->gradable->instructor_id;
    }

    public function grade(User $user, Submission $submission)
    {
        return $user->can('grade-submissions') && $user->id === $submission->gradable->instructor_id;
    }
}