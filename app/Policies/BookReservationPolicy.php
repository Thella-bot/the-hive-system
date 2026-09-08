<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\BookReservation;
use App\Models\User;

class BookReservationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'finance',
            'librarian',
            'student',
        ]);
    }

    public function view(User $user, BookReservation $reservation): bool
    {
        if ($user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian'])) {
            return true;
        }

        return $reservation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'it-support',
            'finance',
            'librarian',
            'student',
        ]);
    }

    public function update(User $user, BookReservation $reservation): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian']);
    }

    public function delete(User $user, BookReservation $reservation): bool
    {
        return $this->update($user, $reservation);
    }

    public function fulfill(User $user, BookReservation $reservation): bool
    {
        return $user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian']);
    }

    public function cancel(User $user, BookReservation $reservation): bool
    {
        if ($user->hasAnyRole(['super-admin', 'it-support', 'finance', 'librarian'])) {
            return true;
        }

        return $reservation->user_id === $user->id
            && $reservation->status === BookReservation::STATUS_PENDING;
    }
}