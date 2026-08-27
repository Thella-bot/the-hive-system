<?php

namespace App\Services;

use App\Models\AcademicHistory;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\StudentGrade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentPromotionService
{
    private const PASS_MARK = 50;

    public function getEligibleStudents(): array
    {
        $currentYear = AcademicYear::current()->first();
        if (!$currentYear) {
            return ['error' => 'No current academic year set.'];
        }

        $students = User::whereHas('profile', function ($q) {
            $q->where('status', 'active');
        })->whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })->get();

        $eligible = [];
        $notEligible = [];
        $graduands = [];

        foreach ($students as $student) {
            $assessment = $this->assessStudent($student, $currentYear);

            if ($assessment['can_graduate']) {
                $graduands[] = $assessment;
            } elseif ($assessment['eligible']) {
                $eligible[] = $assessment;
            } else {
                $notEligible[] = $assessment;
            }
        }

        return [
            'current_year' => $currentYear,
            'eligible' => $eligible,
            'not_eligible' => $notEligible,
            'graduands' => $graduands,
            'total_students' => $students->count(),
        ];
    }

    public function assessStudent(User $student, AcademicYear $currentYear): array
    {
        $profile = $student->profile;
        $cohort = $profile?->cohort;
        $programme = $student->programme;

        if (!$cohort || !$programme) {
            return [
                'student' => $student,
                'eligible' => false,
                'can_graduate' => false,
                'reason' => 'Missing cohort or programme assignment.',
                'year_level' => null,
                'modules_enrolled' => 0,
                'modules_passed' => 0,
                'modules_failed' => 0,
            ];
        }

        $yearLevel = (int) $currentYear->name - (int) $cohort->academicYear->name + 1;
        $semester = now()->month <= 6 ? '1' : '2';

        $programmeModules = $programme->modules()
            ->wherePivot('year_level', $yearLevel)
            ->get();

        $enrolledModules = Enrollment::forStudent($student->id)
            ->forAcademicYear($currentYear->name)
            ->pluck('module_id')
            ->toArray();

        $modulesEnrolled = count($enrolledModules);
        $modulesPassed = 0;
        $modulesFailed = 0;
        $modulesPending = 0;

        foreach ($programmeModules as $module) {
            $grade = StudentGrade::where('student_id', $student->id)
                ->where('gradable_id', $module->id)
                ->first();

            if ($grade) {
                if ($grade->marks >= self::PASS_MARK) {
                    $modulesPassed++;
                } else {
                    $modulesFailed++;
                }
            } else {
                $modulesPending++;
            }
        }

        $totalRequiredModules = $programmeModules->count();
        $allModulesEnrolled = $modulesEnrolled >= $totalRequiredModules;
        $allModulesPassed = $modulesPassed >= $totalRequiredModules && $modulesFailed === 0;

        $isFinalYear = $yearLevel >= $this->getProgrammeDuration($programme);

        $eligible = $allModulesEnrolled && $allModulesPassed && !$isFinalYear;
        $canGraduate = $allModulesEnrolled && $allModulesPassed && $isFinalYear;

        $reason = '';
        if (!$allModulesEnrolled) {
            $reason = "Not all required modules enrolled ({$modulesEnrolled}/{$totalRequiredModules}).";
        } elseif ($modulesFailed > 0) {
            $reason = "Failed {$modulesFailed} module(s).";
        } elseif ($modulesPending > 0) {
            $reason = "{$modulesPending} module(s) pending grades.";
        } elseif ($isFinalYear) {
            $reason = 'Eligible for graduation.';
        } else {
            $reason = 'Eligible for promotion.';
        }

        return [
            'student' => $student,
            'eligible' => $eligible,
            'can_graduate' => $canGraduate,
            'reason' => $reason,
            'year_level' => $yearLevel,
            'semester' => $semester,
            'programme' => $programme,
            'cohort' => $cohort,
            'modules_enrolled' => $modulesEnrolled,
            'modules_passed' => $modulesPassed,
            'modules_failed' => $modulesFailed,
            'modules_pending' => $modulesPending,
            'total_required_modules' => $totalRequiredModules,
            'is_final_year' => $isFinalYear,
        ];
    }

    public function promoteStudent(User $student, AcademicYear $currentYear, string $notes = ''): array
    {
        $assessment = $this->assessStudent($student, $currentYear);

        if (!$assessment['eligible'] && !$assessment['can_graduate']) {
            return [
                'success' => false,
                'message' => $assessment['reason'],
            ];
        }

        try {
            DB::beginTransaction();

            $profile = $student->profile;
            $programme = $student->programme;

            if ($assessment['can_graduate']) {
                $profile->status = 'graduated';
                $profile->graduation_date = now();
                $profile->save();

                AcademicHistory::create([
                    'user_id' => $student->id,
                    'academic_year_id' => $currentYear->id,
                    'programme_id' => $programme->id,
                    'year_level' => $assessment['year_level'],
                    'semester' => $assessment['semester'],
                    'status' => AcademicHistory::STATUS_GRADUATED,
                    'modules_enrolled' => $assessment['modules_enrolled'],
                    'modules_passed' => $assessment['modules_passed'],
                    'modules_failed' => $assessment['modules_failed'],
                    'notes' => $notes ?: 'Graduated from ' . $programme->name,
                    'promoted_at' => now(),
                ]);

                DB::commit();

                Log::info("Student graduated", [
                    'student_id' => $student->id,
                    'student_number' => $profile->student_number,
                    'programme' => $programme->name,
                ]);

                return [
                    'success' => true,
                    'message' => "Student {$student->name} has been graduated.",
                    'graduated' => true,
                ];
            }

            $nextYearLevel = $assessment['year_level'] + 1;
            $nextAcademicYear = AcademicYear::where('name', (string) ((int) $currentYear->name + 1))->first();

            if (!$nextAcademicYear) {
                $nextAcademicYear = AcademicYear::create([
                    'name' => (string) ((int) $currentYear->name + 1),
                    'start_date' => now()->addYear()->startOfYear(),
                    'end_date' => now()->addYear()->endOfYear(),
                    'is_current' => false,
                ]);
            }

            AcademicHistory::create([
                'user_id' => $student->id,
                'academic_year_id' => $currentYear->id,
                'programme_id' => $programme->id,
                'year_level' => $assessment['year_level'],
                'semester' => $assessment['semester'],
                'status' => AcademicHistory::STATUS_PROMOTED,
                'modules_enrolled' => $assessment['modules_enrolled'],
                'modules_passed' => $assessment['modules_passed'],
                'modules_failed' => $assessment['modules_failed'],
                'notes' => $notes ?: "Promoted to year {$nextYearLevel}",
                'promoted_at' => now(),
            ]);

            DB::commit();

            Log::info("Student promoted", [
                'student_id' => $student->id,
                'student_number' => $profile->student_number,
                'from_year' => $assessment['year_level'],
                'to_year' => $nextYearLevel,
            ]);

            return [
                'success' => true,
                'message' => "Student {$student->name} promoted to year {$nextYearLevel}.",
                'graduated' => false,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Failed to promote student", [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to promote student: ' . $e->getMessage(),
            ];
        }
    }

    public function promoteAllEligible(): array
    {
        $results = $this->getEligibleStudents();
        $promoted = [];
        $graduated = [];
        $failed = [];

        foreach ($results['eligible'] ?? [] as $studentData) {
            $result = $this->promoteStudent($studentData['student'], $results['current_year']);
            if ($result['success']) {
                $promoted[] = $result['message'];
            } else {
                $failed[] = $result['message'];
            }
        }

        foreach ($results['graduands'] ?? [] as $studentData) {
            $result = $this->promoteStudent($studentData['student'], $results['current_year']);
            if ($result['success']) {
                $graduated[] = $result['message'];
            } else {
                $failed[] = $result['message'];
            }
        }

        return [
            'promoted' => $promoted,
            'graduated' => $graduated,
            'failed' => $failed,
            'total_promoted' => count($promoted),
            'total_graduated' => count($graduated),
            'total_failed' => count($failed),
        ];
    }

    private function getProgrammeDuration($programme): int
    {
        return (int) ($programme->duration_months / 12) ?: 3;
    }
}
