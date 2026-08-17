<?php
declare(strict_types=1);

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
        if ($user->hasAnyRole(['student', 'chef-instructor', 'pastry-instructor', 'sous-chef'])) {
            if ($gradable && $gradable->due_date && now()->gt($gradable->due_date)) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function update(User $user, Submission $submission)
    {
        return $user->hasAnyRole(['chef-instructor', 'pastry-instructor', 'sous-chef'])
            && $user->id === $submission->gradable->instructor_id;
    }

    public function delete(User $user, Submission $submission)
    {
        return $user->id === $submission->gradable->instructor_id;
    }

    public function grade(User $user, Submission $submission)
    {
        return $user->hasAnyRole(['chef-instructor', 'pastry-instructor', 'sous-chef'])
            && $user->id === $submission->gradable->instructor_id;
    }
}