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
        $isAdmin = auth()->user()?->isAdmin();

        $this->validateBaseFields($input);

        if ($isAdmin) {
            $this->validateAdminFields($input, $staff);
        } else {
            $this->sanitizeNonAdminInput($input);
        }

        $this->updateUserAccount($staff, $isAdmin, $input);

        $this->updateUserRole($staff, $isAdmin, $input);

        $profileData = $this->buildProfileData($staff, $isAdmin, $input);

        $this->updateProfile($staff, $profileData);

        return $staff;
    }

    private function validateBaseFields(array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'designation' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ])->validate();
    }

    private function validateAdminFields(array $input, User $staff): void
    {
        Validator::make($input, [
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($staff->id)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'hire_date' => ['nullable', 'date'],
        ])->validate();
    }

    private function sanitizeNonAdminInput(array &$input): void
    {
        unset($input['email'], $input['role'], $input['department_id'], $input['hire_date']);
    }

    private function updateUserAccount(User $staff, bool $isAdmin, array $input): void
    {
        $userData = ['name' => $input['name']];

        if ($isAdmin && isset($input['email']) && !empty($input['email'])) {
            $userData['email'] = $input['email'];
        }

        if (!empty($input['password'])) {
            $userData['password'] = bcrypt($input['password']);
        }

        $staff->update($userData);
    }

    private function updateUserRole(User $staff, bool $isAdmin, array $input): void
    {
        if (! $isAdmin || empty($input['role'])) {
            return;
        }

        $role = Role::findByName($input['role']);
        $staff->syncRoles([$role]);
    }

    private function buildProfileData(User $staff, bool $isAdmin, array $input): array
    {
        $profileData = [];

        $profileData = $this->buildAdminProfileData($profileData, $input, $isAdmin);

        $profileData = $this->buildStaffProfileData($profileData, $input);

        return $profileData;
    }

    private function buildAdminProfileData(array $profileData, array $input, bool $isAdmin): array
    {
        if (! $isAdmin) {
            return $profileData;
        }

        if (isset($input['department_id'])) {
            $profileData['department_id'] = $input['department_id'];
        }
        if (isset($input['hire_date'])) {
            $profileData['hire_date'] = $input['hire_date'];
        }

        return $profileData;
    }

    private function buildStaffProfileData(array $profileData, array $input): array
    {
        if (isset($input['designation'])) {
            $profileData['designation'] = $input['designation'];
        }
        if (isset($input['specialization'])) {
            $profileData['specialization'] = $input['specialization'];
        }
        if (isset($input['phone'])) {
            $profileData['phone'] = $input['phone'];
        }

        return $profileData;
    }

    private function updateProfile(User $staff, array $profileData): void
    {
        if (!empty($profileData)) {
            $staff->profile()->updateOrCreate(
                ['profileable_id' => $staff->id, 'profileable_type' => User::class],
                $profileData
            );
        }
    }
}