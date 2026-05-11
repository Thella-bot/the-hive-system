<?php namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
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
        return $user->hasAnyRole(['admin', 'instructor', 'hr_staff']);
    }

    public function update(User $user, Announcement $announcement)
    {
        return $user->hasRole('admin') || $user->id === $announcement->created_by;
    }

    public function delete(User $user, Announcement $announcement)
    {
        return $user->hasRole('admin');
    }
}