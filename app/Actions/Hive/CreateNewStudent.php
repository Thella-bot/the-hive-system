<?php

declare(strict_types=1);

namespace App\Actions\Hive;

use App\Models\Cohort;
use App\Models\Profile;
use App\Models\Programme;
use App\Models\User;
use App\Services\IdGenerator;
use App\Services\StudentModuleAssignmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewStudent
{
    /**
     * Create a new student, assign them a role, and enroll them in modules.
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
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'student_number' => ['nullable', 'string', Rule::unique(Profile::class)->where('profileable_type', User::class)],
            'programme_id' => ['nullable', 'exists:programmes,id'],
            'cohort_id' => ['nullable', 'exists:cohorts,id'],
        ])->validate();

        $password = $input['password'] ?? 'password';

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($password),
        ]);

        $user->assignRole('student');

        if (! empty($input['programme_id'])) {
            $programme = Programme::with('modules.department')->find($input['programme_id']);
            if ($programme) {
                app(StudentModuleAssignmentService::class)->sync($user, $programme);

                $department = $programme->department
                    ?? $programme->modules->first()?->department
                    ?? null;

                $cohort = ! empty($input['cohort_id'])
                    ? Cohort::with('academicYear')->find($input['cohort_id'])
                    : null;

                $studentNumber = ! empty($input['student_number'])
                    ? $input['student_number']
                    : ($department ? IdGenerator::generateStudentId($department->id, $this->yearFor($cohort)) : null);

                if ($studentNumber) {
                    $user->profile()->create([
                        'student_number' => $studentNumber,
                        'cohort_id' => $cohort?->id,
                        'enrollment_date' => $cohort?->academicYear?->start_date,
                        // A short course may offer several durations and leave
                        // duration_months blank, so the graduation date is only
                        // calculated when the length is unambiguous.
                        'expected_graduation_date' => $this->graduationFor($cohort, $programme),
                        'status' => 'active',
                    ]);

                    $user->update(['student_number' => $studentNumber]);
                }
            }
        }

        return $user;
    }

    /**
     * The year segment of the student number follows the intake, not the
     * calendar, so an August intake does not get a "2026" number issued in
     * January of the following year.
     */
    protected function yearFor(?Cohort $cohort): ?int
    {
        return $cohort?->academicYear?->start_date?->year;
    }

    /**
     * The expected graduation date, when the programme's length is unambiguous.
     */
    protected function graduationFor(?Cohort $cohort, Programme $programme): ?Carbon
    {
        $start = $cohort?->academicYear?->start_date;
        $months = (int) ($programme->duration_months ?? 0);

        if (! $start || $months <= 0) {
            return null;
        }

        return $start->copy()->addMonths($months);
    }
}
