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
    public function update(User $student, array $input, bool $isAdmin): User
    {
        $this->validateBaseFields($input);

        if ($isAdmin) {
            $this->validateAdminFields($input, $student);
        } else {
            $this->sanitizeNonAdminInput($input);
        }

        $this->updateUserAccount($student, $isAdmin, $input);

        $profileData = $this->buildProfileData($student, $isAdmin, $input);

        $this->updateProfile($student, $profileData);

        $this->syncModuleEnrollments($student, $isAdmin, $input);

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
        Validator::make($input, [
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($student->id)],
            'programme_id' => ['nullable', 'exists:programmes,id'],
            'student_number' => ['nullable', 'string', Rule::unique('profiles')->ignore($student->profile?->id)],
            'cohort_id' => ['nullable', 'exists:cohorts,id'],
            'status' => ['nullable', Rule::in(['active', 'graduated', 'on_leave', 'suspended', 'withdrawn'])],
            'enrollment_date' => ['nullable', 'date'],
            'expected_graduation_date' => ['nullable', 'date'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ])->validate();
    }

    private function sanitizeNonAdminInput(array &$input): void
    {
        unset($input['email'], $input['programme_id'], $input['cohort_id'], $input['status'], $input['enrollment_date'], $input['expected_graduation_date']);
    }

    private function updateUserAccount(User $student, bool $isAdmin, array $input): void
    {
        $userData = ['name' => $input['name']];

        if ($isAdmin) {
            if (isset($input['email']) && !empty($input['email'])) {
                $userData['email'] = $input['email'];
            }
            if (isset($input['programme_id'])) {
                $userData['programme_id'] = $input['programme_id'];
            }
        }

        if (!empty($input['password'])) {
            $userData['password'] = bcrypt($input['password']);
        }

        $student->update($userData);
    }

    private function buildProfileData(User $student, bool $isAdmin, array $input): array
    {
        $profileData = [];

        if ($isAdmin) {
            $profileData = $this->buildAdminProfileData($student, $input);
        }

        $profileData = $this->buildEmergencyContactData($profileData, $input);

        return $profileData;
    }

    private function buildAdminProfileData(User $student, array $input): array
    {
        $profileData = [];

        if (isset($input['student_number'])) {
            $profileData['student_number'] = $input['student_number'];
        }
        if (isset($input['cohort_id'])) {
            $profileData['cohort_id'] = $input['cohort_id'];
        }
        if (isset($input['status'])) {
            $profileData['status'] = $input['status'];
        }
        if (isset($input['first_name'])) {
            $profileData['first_name'] = $input['first_name'];
        }
        if (isset($input['last_name'])) {
            $profileData['last_name'] = $input['last_name'];
        }
        if (isset($input['phone'])) {
            $profileData['phone'] = $input['phone'];
        }
        if (isset($input['address'])) {
            $profileData['address'] = $input['address'];
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

            if ($cohort?->academicYear && !$enrollmentDate) {
                $enrollmentDate = $cohort->academicYear->start_date;
            }

            if ($cohort?->academicYear && $programme?->duration_months && !$graduationDate) {
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
        if (!empty($profileData)) {
            $student->profile()->updateOrCreate(
                ['profileable_id' => $student->id, 'profileable_type' => User::class],
                $profileData
            );
        }
    }

    private function syncModuleEnrollments(User $student, bool $isAdmin, array $input): void
    {
        if (! $isAdmin || !array_key_exists('programme_id', $input)) {
            return;
        }

        if (!empty($input['programme_id'])) {
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