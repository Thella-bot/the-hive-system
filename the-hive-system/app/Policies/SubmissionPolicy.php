<?php namespace App\Policies;
use App\Models\Submission;
use App\Models\User;
class SubmissionPolicy
{
    public function view(User $user, Submission $submission) {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('instructor') && $submission->assignment->instructor_id === $user->id) return true;
        if ($user->hasRole('student') && $submission->student_id === $user->id) return true;
        return false;
    }
    public function create(User $user) { return $user->hasRole('student'); }
    public function update(User $user, Submission $submission) {
        return $user->hasRole('admin') ||
               ($user->hasRole('instructor') && $submission->assignment->instructor_id === $user->id);
    }
}