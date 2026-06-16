<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy extends BasePolicy
{
    public function viewAny(User $user) { return true; }

    public function view(User $user, Application $application)
    {
        // Own application by user_id or email
        if ($user->id === $application->user_id) return true;
        if ($user->email === $application->email) return true;
        // Admissions officers and admin can view
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'admissions-officer',
            'academic-director',
        ]);
    }

    public function create(User $user) { return true; }

    public function update(User $user, Application $application)
    {
        // Admissions officers and admin can update status/notes
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'admissions-officer',
            'registrar',
        ]);
    }

    public function delete(User $user, Application $application)
    {
        return $user->hasAnyRole(['super-admin', 'it-support']);
    }
}
