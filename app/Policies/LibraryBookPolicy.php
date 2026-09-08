<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\LibraryBook;
use App\Models\User;

class LibraryBookPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
            'admissions-officer',
            'examination-cell',
            'registrar',
            'finance',
            'procurement-manager',
            'storekeeper',
            'hr-manager',
            'librarian',
            'career-services',
            'events-pr-manager',
            'cafeteria-manager',
            'student',
        ]);
    }

    public function view(User $user, LibraryBook $book): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
            'admissions-officer',
            'examination-cell',
            'registrar',
            'finance',
            'procurement-manager',
            'storekeeper',
            'hr-manager',
            'librarian',
            'career-services',
            'events-pr-manager',
            'cafeteria-manager',
        ]);
    }

    public function update(User $user, LibraryBook $book): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, LibraryBook $book): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian']);
    }
}