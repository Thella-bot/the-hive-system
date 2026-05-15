<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Submission $submission)
    {
        return $user->id === $submission->student_id || $user->id === $submission->assignment->instructor_id || $user->hasRole('admin');
    }

    public function create(User $user)
    {
        return $user->hasRole('student');
    }

    public function update(User $user, Submission $submission)
    {
        return $user->id === $submission->assignment->instructor_id || $user->hasRole('admin');
    }

    public function delete(User $user, Submission $submission)
    {
        return $user->id === $submission->assignment->instructor_id || $user->hasRole('admin');
    }
}
