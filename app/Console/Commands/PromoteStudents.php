<?php

namespace App\Console\Commands;

use App\Services\StudentPromotionService;
use Illuminate\Console\Command;

class PromoteStudents extends Command
{
    protected $signature = 'students:promote
                            {--dry-run : Show what would be promoted without making changes}
                            {--student= : Promote a specific student by ID}
                            {--year= : Target academic year}';

    protected $description = 'Promote eligible students to the next academic year';

    public function __construct(
        private StudentPromotionService $promotionService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $studentId = $this->option('student');

        if ($studentId) {
            return $this->promoteSpecificStudent((int) $studentId, $dryRun);
        }

        return $this->promoteAllEligible($dryRun);
    }

    private function promoteSpecificStudent(int $studentId, bool $dryRun): int
    {
        $student = \App\Models\User::find($studentId);

        if (!$student) {
            $this->error("Student with ID {$studentId} not found.");
            return Command::FAILURE;
        }

        $currentYear = \App\Models\AcademicYear::current()->first();

        if (!$currentYear) {
            $this->error('No current academic year set.');
            return Command::FAILURE;
        }

        $assessment = $this->promotionService->assessStudent($student, $currentYear);

        $this->displayStudentAssessment($assessment);

        if ($dryRun) {
            return Command::SUCCESS;
        }

        if (!$assessment['eligible'] && !$assessment['can_graduate']) {
            $this->warn("Student is not eligible: {$assessment['reason']}");
            return Command::SUCCESS;
        }

        $result = $this->promotionService->promoteStudent($student, $currentYear);

        if ($result['success']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        }

        $this->error($result['message']);
        return Command::FAILURE;
    }

    private function promoteAllEligible(bool $dryRun): int
    {
        $results = $this->promotionService->getEligibleStudents();

        if (isset($results['error'])) {
            $this->error($results['error']);
            return Command::FAILURE;
        }

        $currentYear = $results['current_year'];

        $this->info("Academic Year: {$currentYear->name}");
        $this->info("Total Students: {$results['total_students']}");
        $this->info('');

        $this->info('Eligible for Promotion: ' . count($results['eligible']));
        foreach ($results['eligible'] as $data) {
            $this->line("  - {$data['student']->name} (Year {$data['year_level']}) - {$data['reason']}");
        }

        $this->info('');
        $this->info('Eligible for Graduation: ' . count($results['graduands']));
        foreach ($results['graduands'] as $data) {
            $this->line("  - {$data['student']->name} (Year {$data['year_level']}) - {$data['reason']}");
        }

        $this->info('');
        $this->info('Not Eligible: ' . count($results['not_eligible']));
        foreach ($results['not_eligible'] as $data) {
            $this->line("  - {$data['student']->name} - {$data['reason']}");
        }

        if ($dryRun) {
            $this->info('');
            $this->info('Dry run - no changes made.');
            return Command::SUCCESS;
        }

        if (count($results['eligible']) === 0 && count($results['graduands']) === 0) {
            $this->info('');
            $this->info('No students eligible for promotion.');
            return Command::SUCCESS;
        }

        $this->info('');
        if (!$this->confirm('Proceed with promotion?')) {
            return Command::SUCCESS;
        }

        $promotionResults = $this->promotionService->promoteAllEligible();

        $this->info('');
        $this->info("Promoted: {$promotionResults['total_promoted']}");
        $this->info("Graduated: {$promotionResults['total_graduated']}");

        if ($promotionResults['total_failed'] > 0) {
            $this->warn("Failed: {$promotionResults['total_failed']}");
            foreach ($promotionResults['failed'] as $fail) {
                $this->error("  - {$fail}");
            }
        }

        return Command::SUCCESS;
    }

    private function displayStudentAssessment(array $assessment): void
    {
        $this->info('Student Assessment');
        $this->info('------------------');
        $this->line("Name: {$assessment['student']->name}");
        $this->line("Year Level: {$assessment['year_level']}");
        $this->line("Modules Enrolled: {$assessment['modules_enrolled']}/{$assessment['total_required_modules']}");
        $this->line("Modules Passed: {$assessment['modules_passed']}");
        $this->line("Modules Failed: {$assessment['modules_failed']}");
        $this->line("Status: {$assessment['reason']}");

        if ($assessment['can_graduate']) {
            $this->info('Status: READY TO GRADUATE');
        } elseif ($assessment['eligible']) {
            $this->info('Status: ELIGIBLE FOR PROMOTION');
        } else {
            $this->warn('Status: NOT ELIGIBLE');
        }
    }
}
