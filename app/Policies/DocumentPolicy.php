<?php namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy extends BasePolicy
{
    public function viewAny(User $user) { return true; }

    public function view(User $user, Document $document)
    {
        // If document has visibility restriction, check user's roles
        if ($document->visible_to_roles) {
            return $user->hasAnyRole($document->visible_to_roles);
        }
        return true; // public to all Hive users
    }

    public function create(User $user)
    {
        return $user->hasAnyRole([
            'super-admin', 'school-admin', 'academic_staff',
            'department-head', 'chef-instructor', 'non_academic_staff'
        ]);
    }

    public function update(User $user, Document $document)
    {
        // Admins can update any, others only their own
        if ($user->hasAnyRole(['super-admin', 'school-admin'])) {
            return true;
        }
        return $user->id === $document->created_by;
    }

    public function delete(User $user, Document $document)
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']);
    }
}