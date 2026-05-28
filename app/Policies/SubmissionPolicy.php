<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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

    public function create(User $user)
    {
        return $user->can('create-submissions');
    }

    public function update(User $user, Submission $submission)
    {
        return $user->id === $submission->gradable->instructor_id;
    }

    public function delete(User $user, Submission $submission)
    {
        return $user->id === $submission->gradable->instructor_id;
    }
}