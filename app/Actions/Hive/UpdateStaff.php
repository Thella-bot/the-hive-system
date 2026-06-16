<?php

namespace App\Actions\Hive;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateStaff
{
    public function update(User $staff, array $input): User
    {
        // Check if user is admin
        $isAdmin = auth()->user()?->isAdmin();

        // Base fields validation (available to everyone)
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'designation' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ])->validate();

        // Admin-only fields validation
        if ($isAdmin) {
            Validator::make($input, [
                'email' => ['nullable', 'email', Rule::unique('users')->ignore($staff->id)],
                'role' => ['required', 'string', 'exists:roles,name'],
                'department_id' => ['nullable', 'exists:departments,id'],
                'hire_date' => ['nullable', 'date'],
            ])->validate();
        } else {
            // Non-admins cannot change these sensitive fields
            unset($input['email'], $input['role'], $input['department_id'], $input['hire_date']);
        }

        // Update user account
        $userData = ['name' => $input['name']];

        // Only admins can change email
        if ($isAdmin && isset($input['email']) && !empty($input['email'])) {
            $userData['email'] = $input['email'];
        }

        // Update password if provided
        if (!empty($input['password'])) {
            $userData['password'] = bcrypt($input['password']);
        }

        $staff->update($userData);

        // Only admins can change role
        if ($isAdmin && !empty($input['role'])) {
            $role = Role::findByName($input['role']);
            $staff->syncRoles([$role]);
        }

        // Update profile
        $profileData = [];

        // Admin-only fields
        if ($isAdmin) {
            if (isset($input['department_id'])) {
                $profileData['department_id'] = $input['department_id'];
            }
            if (isset($input['hire_date'])) {
                $profileData['hire_date'] = $input['hire_date'];
            }
        }

        // Available to all staff
        if (isset($input['designation'])) {
            $profileData['designation'] = $input['designation'];
        }
        if (isset($input['specialization'])) {
            $profileData['specialization'] = $input['specialization'];
        }
        if (isset($input['phone'])) {
            $profileData['phone'] = $input['phone'];
        }

        if (!empty($profileData)) {
            $staff->profile()->updateOrCreate(
                ['profileable_id' => $staff->id, 'profileable_type' => User::class],
                $profileData
            );
        }

        return $staff;
    }
}