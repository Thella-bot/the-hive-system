<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cohort;
use App\Models\Profile;
use App\Models\Programme;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keeps a student's department-bound records coherent when they move to a
 * programme in a different department.
 *
 * A student number is "{PREFIX}{YEAR}{DEPARTMENT}{SEQUENCE}", so the number
 * embeds the department. When a transfer changes the department the old number
 * silently keeps pointing at the previous department, which then shows up as
 * misfiled transcripts, ID cards and finance records. This service reissues the
 * number in the new department's series and moves the cohort with them.
 */
class ProgrammeTransferService
{
    public function __construct(
        protected StudentNumberService $studentNumbers,
        protected StudentModuleAssignmentService $moduleAssignments,
    ) {}

    /**
     * Apply a programme change, keeping dependent records consistent.
     *
     * @param  array{student_number?: string|null, cohort_id?: int|null, enrollment_date?: string|null, expected_graduation_date?: string|null}  $input
     * @param  Programme|null  $previousProgramme  The programme held before this
     *                                             call. Passed in because the
     *                                             user row is already rewritten
     *                                             by the time this runs.
     * @return array{programme: ?Programme, department_id: ?int, previous_department_id: ?int, student_number_from: ?string, student_number_to: ?string, cohort_from: ?int, cohort_to: ?int}
     */
    public function transfer(User $student, ?Programme $target, array $input = [], ?Programme $previousProgramme = null): array
    {
        $student->loadMissing(['profile.cohort']);

        $profile = $student->profile;
        $previousDepartmentId = $this->departmentIdFor($previousProgramme, $profile?->cohort);

        if (! $target) {
            return $this->emptyResult($student, $previousDepartmentId);
        }

        $departmentId = $this->departmentIdFor($target, $profile?->cohort);
        $departmentChanged = $departmentId !== null && $departmentId !== $previousDepartmentId;

        $result = DB::transaction(function () use ($student, $profile, $target, $input, $departmentId, $departmentChanged, $previousDepartmentId) {
            $cohortFrom = $profile?->cohort_id;
            $numberFrom = $student->student_number ?? $profile?->student_number;

            if ($departmentChanged) {
                $this->relocateCohort($profile, $target, $departmentId, $input);
            }

            $cohortTo = $profile?->fresh()?->cohort_id ?? $cohortFrom;

            if ($departmentChanged) {
                $this->refreshGraduationDate($profile, $target, $cohortTo, $input);
            }

            // Always run, so a number typed by an administrator is mirrored onto
            // both the user row and the profile; only the automatic reissue is
            // limited to actual department moves.
            $numberTo = $this->reissueNumber(
                $student,
                $departmentId,
                $profile?->enrollment_date,
                $input,
                $departmentChanged,
            );

            $student->update(['programme_id' => $target->id]);
            $this->moduleAssignments->sync($student, $target);

            return [
                'programme' => $target,
                'department_id' => $departmentId,
                'previous_department_id' => $previousDepartmentId,
                'student_number_from' => $numberFrom,
                'student_number_to' => $numberTo,
                'cohort_from' => $cohortFrom,
                'cohort_to' => $cohortTo,
            ];
        });

        $this->logTransfer($student, $result);

        return $result;
    }

    /**
     * Keep the student number in step with the student's department.
     *
     * A number supplied by an administrator is always written to both the user
     * row and the profile, so the two can never drift apart. A number is only
     * generated when $allowReissue is set and the current one no longer encodes
     * the student's department, so an unrelated edit never renumbers a student.
     *
     * @return string|null The number the student now holds.
     */
    public function reissueNumber(
        User $student,
        ?int $departmentId,
        mixed $enrollmentDate = null,
        array $input = [],
        bool $allowReissue = false,
    ): ?string {
        $current = $student->student_number ?? $student->profile?->student_number;

        if (array_key_exists('student_number', $input)) {
            $requested = $input['student_number'] ?: null;

            if ($requested !== null && $requested !== $current) {
                $this->applyNumber($student, $requested);

                return $requested;
            }

            return $current;
        }

        if (! $allowReissue || $departmentId === null) {
            return $current;
        }

        $year = $enrollmentDate ? Carbon::parse($enrollmentDate)->year : null;

        if ($current && $this->studentNumbers->matches($current, $departmentId, $year)) {
            return $current;
        }

        $reissued = IdGenerator::generateStudentId($departmentId, $year);
        $this->applyNumber($student, $reissued);

        return $reissued;
    }

    /**
     * A student number is stored in two places; they must never drift.
     */
    protected function applyNumber(User $student, string $number): void
    {
        $student->update(['student_number' => $number]);

        $student->profile()->updateOrCreate(
            ['profileable_id' => $student->id, 'profileable_type' => User::class],
            ['student_number' => $number],
        );
    }

    /**
     * Move the student to the cohort in the new department that matches their
     * current intake (same academic year and intake period). Leaves the student
     * where they are when no counterpart exists, so nothing is silently lost.
     */
    protected function relocateCohort(?Profile $profile, Programme $target, ?int $departmentId, array $input): void
    {
        if (! $profile) {
            return;
        }

        if (! empty($input['cohort_id'])) {
            $profile->update(['cohort_id' => $input['cohort_id']]);

            return;
        }

        $current = $profile->cohort;
        if (! $current || $current->department_id === $departmentId) {
            return;
        }

        $match = $this->matchingCohort($current, $departmentId);
        if (! $match) {
            Log::warning('Programme transfer left the student in a cross-department cohort.', [
                'student_id' => $profile->profileable_id,
                'cohort_id' => $current->id,
                'target_department_id' => $departmentId,
            ]);

            return;
        }

        $profile->update(['cohort_id' => $match->id]);
    }

    /**
     * Find the cohort in $departmentId mirroring $source's intake.
     */
    protected function matchingCohort(Cohort $source, int $departmentId): ?Cohort
    {
        return Cohort::query()
            ->where('department_id', $departmentId)
            ->when(
                $source->academic_year_id,
                fn ($query) => $query->where('academic_year_id', $source->academic_year_id)
            )
            ->where('name', $source->name)
            ->orderByDesc('id')
            ->first()
            ?? Cohort::query()
                ->where('department_id', $departmentId)
                ->when(
                    $source->academic_year_id,
                    fn ($query) => $query->where('academic_year_id', $source->academic_year_id)
                )
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first();
    }

    /**
     * Recalculate the expected graduation date for the new programme duration,
     * unless the administrator supplied one.
     */
    protected function refreshGraduationDate(?Profile $profile, Programme $target, ?int $cohortId, array $input): void
    {
        if (! $profile || array_key_exists('expected_graduation_date', $input)) {
            return;
        }

        // A programme with no recorded length (short courses can offer several,
        // e.g. "3 Months / 6 Months") is left for a human to set rather than
        // guessed, which would place the date years out.
        $months = (int) ($target->duration_months ?? 0);

        if ($months <= 0) {
            return;
        }

        $start = $input['enrollment_date']
            ?? $profile->enrollment_date
            ?? ($cohortId ? Cohort::find($cohortId)?->academicYear?->start_date : null);

        if (! $start) {
            return;
        }

        $profile->update([
            'expected_graduation_date' => Carbon::parse($start)->addMonths($months),
        ]);
    }

    /**
     * The department a student belongs to, taken from their programme and
     * falling back to the cohort when the programme is unset.
     */
    public function departmentIdFor(?Programme $programme, ?Cohort $cohort = null): ?int
    {
        if ($programme?->department_id) {
            return (int) $programme->department_id;
        }

        return $cohort?->department_id !== null ? (int) $cohort->department_id : null;
    }

    protected function emptyResult(User $student, ?int $previousDepartmentId): array
    {
        return [
            'programme' => null,
            'department_id' => null,
            'previous_department_id' => $previousDepartmentId,
            'student_number_from' => $student->student_number,
            'student_number_to' => $student->student_number,
            'cohort_from' => $student->profile?->cohort_id,
            'cohort_to' => $student->profile?->cohort_id,
        ];
    }

    protected function logTransfer(User $student, array $result): void
    {
        if ($result['student_number_from'] === $result['student_number_to']
            && $result['cohort_from'] === $result['cohort_to']) {
            return;
        }

        Log::info('Student programme transfer adjusted department-bound records.', [
            'student_id' => $student->id,
            'programme_id' => $result['programme']?->id,
            'department_from' => $result['previous_department_id'],
            'department_to' => $result['department_id'],
            'student_number_from' => $result['student_number_from'],
            'student_number_to' => $result['student_number_to'],
            'cohort_from' => $result['cohort_from'],
            'cohort_to' => $result['cohort_to'],
        ]);
    }
}
