<?php namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user) { return true; }

    public function view(User $user, Document $document)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // If document has visibility restriction, check user's roles
        if ($document->visible_to_roles) {
            return $user->hasAnyRole($document->visible_to_roles);
        }
        return true; // public to all Hive users
    }

    public function create(User $user)
    {
        return $user->hasAnyRole(['admin', 'hr_staff', 'instructor']);
    }

    public function update(User $user, Document $document)
    {
        return $user->hasRole('admin') || $user->id === $document->created_by;
    }

    public function delete(User $user, Document $document)
    {
        return $user->hasRole('admin');
    }
}
