<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\BookLoan;
use App\Models\User;

class BookLoanPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian']);
    }

    public function view(User $user, BookLoan $loan): bool
    {
        if ($user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian'])) {
            return true;
        }

        return $loan->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian']);
    }

    public function update(User $user, BookLoan $loan): bool
    {
        if ($user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian'])) {
            return true;
        }

        // Borrowers can return/renew their own loans
        return $loan->user_id === $user->id;
    }

    public function delete(User $user, BookLoan $loan): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian']);
    }

    public function renew(User $user, BookLoan $loan): bool
    {
        return $this->update($user, $loan);
    }

    public function returnBook(User $user, BookLoan $loan): bool
    {
        return $this->update($user, $loan);
    }
}