<?php

declare(strict_types=1);

namespace App\Actions\Hive;

use App\Models\Cohort;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateStudent
{
    public function update(User $student, array $input, bool $canManageAllFields): User
    {
        $this->validateBaseFields($input);

        if ($canManageAllFields) {
            $this->validateAdminFields($input, $student);
        } else {
            $this->sanitizeNonAdminInput($input);
        }

        $this->updateUserAccount($student, $canManageAllFields, $input);

        $profileData = $this->buildProfileData($student, $canManageAllFields, $input);

        $this->updateProfile($student, $profileData);

        $this->syncModuleEnrollments($student, $canManageAllFields, $input);

        return $student;
    }

    private function validateBaseFields(array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
        ])->validate();
    }

    private function validateAdminFields(array $input, User $student): void
    {
        $profileId = $student->profile?->id;

        Validator::make($input, [
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($student->id)],
            'programme_id' => ['nullable', 'exists:programmes,id'],
            'student_number' => ['nullable', 'string', Rule::unique('profiles')->ignore($profileId ?: 0, 'id')],
            'cohort_id' => ['nullable', 'exists:cohorts,id'],
            'status' => ['nullable', Rule::in(['active', 'graduated', 'on_leave', 'suspended', 'withdrawn'])],
            'enrollment_date' => ['nullable', 'date'],
            'expected_graduation_date' => ['nullable', 'date'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'national_id_number' => ['nullable', 'string', 'max:100'],
        ])->validate();
    }

    private function sanitizeNonAdminInput(array &$input): void
    {
        unset($input['email'], $input['programme_id'], $input['cohort_id'], $input['status'], $input['enrollment_date'], $input['expected_graduation_date'], $input['student_number'], $input['first_name'], $input['last_name'], $input['phone'], $input['address'], $input['date_of_birth'], $input['gender'], $input['national_id_number']);
    }

    private function updateUserAccount(User $student, bool $canManageAllFields, array $input): void
    {
        $userData = ['name' => $input['name']];

        if ($canManageAllFields) {
            if (array_key_exists('email', $input)) {
                $userData['email'] = $input['email'] ?? '';
            }
            if (array_key_exists('programme_id', $input)) {
                $userData['programme_id'] = $input['programme_id'] ?: null;
            }
            if (array_key_exists('gender', $input)) {
                $userData['gender'] = $input['gender'] ?: null;
            }
            if (array_key_exists('national_id_number', $input)) {
                $userData['national_id_number'] = $input['national_id_number'] ?: null;
            }
        }

        if (! empty($input['password'])) {
            $userData['password'] = bcrypt($input['password']);
        }

        $student->update($userData);
    }

    private function buildProfileData(User $student, bool $canManageAllFields, array $input): array
    {
        $profileData = [];

        if ($canManageAllFields) {
            $profileData = $this->buildAdminProfileData($student, $input);
        }

        $profileData = $this->buildEmergencyContactData($profileData, $input);

        return $profileData;
    }

    private function buildAdminProfileData(User $student, array $input): array
    {
        $profileData = [];

        if (array_key_exists('student_number', $input)) {
            $profileData['student_number'] = $input['student_number'] ?: null;
        }
        if (array_key_exists('cohort_id', $input)) {
            $profileData['cohort_id'] = $input['cohort_id'] ?: null;
        }
        if (array_key_exists('status', $input)) {
            $profileData['status'] = $input['status'] ?: 'active';
        }
        if (array_key_exists('first_name', $input)) {
            $profileData['first_name'] = $input['first_name'] ?: null;
        }
        if (array_key_exists('last_name', $input)) {
            $profileData['last_name'] = $input['last_name'] ?: null;
        }
        if (array_key_exists('phone', $input)) {
            $profileData['phone'] = $input['phone'] ?: null;
        }
        if (array_key_exists('address', $input)) {
            $profileData['address'] = $input['address'] ?: null;
        }
        if (array_key_exists('date_of_birth', $input)) {
            $profileData['date_of_birth'] = $input['date_of_birth'] ?: null;
        }

        $dates = $this->calculateEnrollmentAndGraduationDates($student, $input);
        if ($dates['enrollment_date']) {
            $profileData['enrollment_date'] = $dates['enrollment_date'];
        }
        if ($dates['expected_graduation_date']) {
            $profileData['expected_graduation_date'] = $dates['expected_graduation_date'];
        }

        return $profileData;
    }

    private function calculateEnrollmentAndGraduationDates(User $student, array $input): array
    {
        $enrollmentDate = $input['enrollment_date'] ?? null;
        $graduationDate = $input['expected_graduation_date'] ?? null;

        if (isset($input['cohort_id']) || isset($input['programme_id'])) {
            $cohort = isset($input['cohort_id'])
                ? Cohort::with('academicYear', 'department.programmes')->find($input['cohort_id'])
                : $student->profile?->cohort;

            $programme = isset($input['programme_id'])
                ? Programme::find($input['programme_id'])
                : $student->programme;

            if ($cohort?->academicYear && ! $enrollmentDate) {
                $enrollmentDate = $cohort->academicYear->start_date;
            }

            if ($cohort?->academicYear && $programme?->duration_months && ! $graduationDate) {
                $enrollDate = $cohort->academicYear->start_date ?? now();
                $graduationDate = $enrollDate->copy()->addMonths($programme->duration_months);
            }
        }

        return [
            'enrollment_date' => $enrollmentDate,
            'expected_graduation_date' => $graduationDate,
        ];
    }

    private function buildEmergencyContactData(array $profileData, array $input): array
    {
        if (isset($input['emergency_contact_name'])) {
            $profileData['emergency_contact_name'] = $input['emergency_contact_name'];
        }
        if (isset($input['emergency_contact_phone'])) {
            $profileData['emergency_contact_phone'] = $input['emergency_contact_phone'];
        }
        if (isset($input['emergency_contact_relationship'])) {
            $profileData['emergency_contact_relationship'] = $input['emergency_contact_relationship'];
        }

        return $profileData;
    }

    private function updateProfile(User $student, array $profileData): void
    {
        if (! empty($profileData)) {
            $student->profile()->updateOrCreate(
                ['profileable_id' => $student->id, 'profileable_type' => User::class],
                $profileData
            );
        }
    }

    private function syncModuleEnrollments(User $student, bool $canManageAllFields, array $input): void
    {
        if (! $canManageAllFields || ! array_key_exists('programme_id', $input)) {
            return;
        }

        if (! empty($input['programme_id'])) {
            $programme = Programme::find($input['programme_id']);
            if ($programme) {
                $moduleIds = $programme->modules()->pluck('id');
                $student->modules()->sync($moduleIds);
            }
        } else {
            $student->modules()->sync([]);
        }
    }
}
