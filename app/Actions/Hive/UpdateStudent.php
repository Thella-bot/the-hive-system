<?php

namespace App\Actions\Hive;

use App\Models\Cohort;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateStudent
{
    public function update(User $student, array $input): User
    {
        $isAdmin = $student->hasAnyRole(['super-admin', 'school-admin']);

        // Validate base fields (available to everyone)
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
        ])->validate();

        // Admin-only fields validation
        if ($isAdmin) {
            Validator::make($input, [
                'email' => ['nullable', 'email', Rule::unique('users')->ignore($student->id)],
                'programme_id' => ['nullable', 'exists:programmes,id'],
                'cohort_id' => ['nullable', 'exists:cohorts,id'],
                'status' => ['nullable', Rule::in(['active', 'graduated', 'on_leave', 'suspended', 'withdrawn'])],
                'enrollment_date' => ['nullable', 'date'],
                'expected_graduation_date' => ['nullable', 'date'],
            ])->validate();
        } else {
            // Non-admins cannot change these fields - remove from input
            unset($input['email'], $input['programme_id'], $input['cohort_id'], $input['status'], $input['enrollment_date'], $input['expected_graduation_date']);
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

        $student->update($userData);

        // Update profile
        $profileData = [];

        // Sensitive fields - admin only
        if ($isAdmin) {
            if (isset($input['cohort_id'])) {
                $profileData['cohort_id'] = $input['cohort_id'];
            }
            if (isset($input['status'])) {
                $profileData['status'] = $input['status'];
            }

            // Auto-calculate enrollment and graduation dates
            $enrollmentDate = $input['enrollment_date'] ?? null;
            $graduationDate = $input['expected_graduation_date'] ?? null;

            // If cohort is set, calculate dates from academic year and programme duration
            if (isset($input['cohort_id']) || isset($input['programme_id'])) {
                $cohort = isset($input['cohort_id'])
                    ? Cohort::with('academicYear', 'department.programme')->find($input['cohort_id'])
                    : $student->profile?->cohort;

                $programme = isset($input['programme_id'])
                    ? Programme::find($input['programme_id'])
                    : $cohort?->department?->programme;

                if ($cohort?->academicYear && !$enrollmentDate) {
                    // Auto-set enrollment date from academic year start
                    $enrollmentDate = $cohort->academicYear->start_date;
                }

                if ($cohort?->academicYear && $programme?->duration_months && !$graduationDate) {
                    // Calculate graduation: enrollment + programme duration
                    $enrollDate = $cohort->academicYear->start_date ?? now();
                    $graduationDate = $enrollDate->copy()->addMonths($programme->duration_months);
                }
            }

            if ($enrollmentDate) {
                $profileData['enrollment_date'] = $enrollmentDate;
            }
            if ($graduationDate) {
                $profileData['expected_graduation_date'] = $graduationDate;
            }
        }

        // Emergency contact - available to all
        if (isset($input['emergency_contact_name'])) {
            $profileData['emergency_contact_name'] = $input['emergency_contact_name'];
        }
        if (isset($input['emergency_contact_phone'])) {
            $profileData['emergency_contact_phone'] = $input['emergency_contact_phone'];
        }
        if (isset($input['emergency_contact_relationship'])) {
            $profileData['emergency_contact_relationship'] = $input['emergency_contact_relationship'];
        }

        if (!empty($profileData)) {
            $student->profile()->updateOrCreate(
                ['profileable_id' => $student->id, 'profileable_type' => User::class],
                $profileData
            );
        }

        // Update module enrollments when programme changes (admin only)
        if ($isAdmin && array_key_exists('programme_id', $input)) {
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

        return $student;
    }
}