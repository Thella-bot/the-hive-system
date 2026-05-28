<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy extends BasePolicy
{
    public function viewAny(User $user) { return true; }

    public function view(User $user, Application $application)
    {
        // Own application or admin can view
        if ($user->id === $application->user_id) return true;
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']);
    }

    public function create(User $user) { return true; }

    public function update(User $user, Application $application)
    {
        // Admins and school-admin can update status/notes
        if ($user->hasAnyRole(['super-admin', 'school-admin'])) return true;
        return false;
    }

    public function delete(User $user, Application $application)
    {
        return $user->hasRole('super-admin');
    }
}
