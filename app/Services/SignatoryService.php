<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class SignatoryService
{
    public function get(string $role): string
    {
        $user = User::role($role)->first();

        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }
}
