<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class SignatoryService
{
    public function get(string $role): string
    {
        $user = cache()->rememberForever("signatory.{$role}", function () use ($role) {
            return User::role($role)->first();
        });

        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }
}
