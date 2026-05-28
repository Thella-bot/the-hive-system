<?php

namespace App\Actions\Hive;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateStaff
{
    /**
     * Update an existing staff member's information.
     *
     * @param  \App\Models\User  $staff
     * @param  array  $input
     * @return \App\Models\User
     */
    public function update(User $staff, array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($staff->id),
            ],
            'role_id' => ['required', 'exists:roles,id'],
        ])->validate();

        $staff->update([
            'name' => $input['name'],
            'email' => $input['email'],
        ]);

        $role = Role::findById($input['role_id']);
        $staff->syncRoles([$role]);

        return $staff;
    }
}
