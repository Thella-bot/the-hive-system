<?php namespace App\Policies;
use App\Models\Assignment;
use App\Models\User;
class AssignmentPolicy
{
    public function viewAny(User $user) { return $user->hasRole(['instructor','admin','student']); }
    public function create(User $user) { return $user->hasRole(['instructor','admin']); }
    public function update(User $user, Assignment $assignment) {
        return $user->hasRole('admin') || ($user->hasRole('instructor') && $user->id === $assignment->instructor_id);
    }
    public function view(User $user, Assignment $assignment) {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('instructor') && $user->id === $assignment->instructor_id) return true;
        if ($user->hasRole('student')) {
            return $assignment->module->students()->where('user_id', $user->id)->exists();
        }
        return false;
    }
}