<?php namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy extends BasePolicy
{
    public function viewAny(User $user) { return true; }

    public function view(User $user, Announcement $announcement)
    {
        if ($announcement->target_roles) {
            return $user->hasAnyRole($announcement->target_roles);
        }
        return true;
    }

    public function create(User $user)
    {
        return $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']);
    }

    public function update(User $user, Announcement $announcement)
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']) || $user->id === $announcement->created_by;
    }

    public function delete(User $user, Announcement $announcement)
    {
        return $user->hasAnyRole(['super-admin', 'school-admin']) || $user->id === $announcement->created_by;
    }
}
