<?php

namespace App\Actions\Hive;

use App\Models\User;
use App\Services\IdGenerator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class CreateNewStaff
{
    /**
     * Create a new staff user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make('password'),
        ]);

        $role = Role::findById($input['role_id']);
        $user->assignRole($role);

        if (!empty($input['department_id'])) {
            $employeeId = IdGenerator::generateEmployeeId($input['department_id']);
            $user->profile()->create([
                'employee_number' => $employeeId,
                'department_id' => $input['department_id'],
            ]);
        }

        return $user;
    }
}
