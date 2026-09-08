<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\BookCategory;
use App\Models\User;

class BookCategoryPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian']);
    }

    public function view(User $user, BookCategory $category): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian']);
    }

    public function update(User $user, BookCategory $category): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, BookCategory $category): bool
    {
        return $this->create($user);
    }
}