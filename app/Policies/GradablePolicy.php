<?php

namespace App\Policies;

use App\Models\Gradable;
use App\Models\User;

class GradablePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Gradable $gradable): bool
    {
        // Admin can view all
        if ($user->hasAnyRole(['super-admin', 'it-support', 'academic-director'])) {
            return true;
        }

        // Instructor can view their own gradables
        if ($user->hasAnyRole(['chef-instructor', 'pastry-instructor', 'sous-chef']) && $gradable->instructor_id === $user->id) {
            return true;
        }

        // Students can view enrolled gradables
        if ($user->hasRole('student')) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Admin and faculty can create gradables
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'chef-instructor',
            'pastry-instructor',
            'sous-chef',
            'examination-cell',
        ]);
    }

    public function update(User $user, Gradable $gradable): bool
    {
        // Admin can update all
        if ($user->hasAnyRole(['super-admin', 'it-support', 'academic-director'])) {
            return true;
        }

        // Instructor can update their own gradables
        return $user->hasAnyRole(['chef-instructor', 'pastry-instructor', 'sous-chef'])
            && $gradable->instructor_id === $user->id;
    }

    public function delete(User $user, Gradable $gradable): bool
    {
        // Admin can delete all
        if ($user->hasAnyRole(['super-admin', 'it-support', 'academic-director'])) {
            return true;
        }

        // Instructor can delete their own gradables
        return $user->hasAnyRole(['chef-instructor', 'pastry-instructor', 'sous-chef'])
            && $gradable->instructor_id === $user->id;
    }
}