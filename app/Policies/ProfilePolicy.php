<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfilePolicy extends BasePolicy
{
    public function view(User $user, Profile $profile)
    {
        return $user->id === $profile->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Profile $profile)
    {
        return $user->id === $profile->user_id;
    }
}