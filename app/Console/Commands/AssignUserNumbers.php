<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Profile;
use App\Models\Department;
use App\Services\IdGenerator;
use Illuminate\Console\Command;

class AssignUserNumbers extends Command
{
    protected $signature = 'users:assign-numbers
                            {--dry-run : Show what would be assigned without making changes}
                            {--type= : Filter by type (student or staff)}';

    protected $description = 'Assign student/employee numbers to users who do not have them';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $type = $this->option('type');

        $departments = Department::active()->get();

        if ($departments->isEmpty()) {
            $this->error('No active departments found.');
            return Command::FAILURE;
        }

        // Default department for users without one
        $defaultDept = $departments->first();

        $assignments = [];

        // Get students without student_number in profile
        if (!$type || $type === 'student') {
            $students = User::role('student')
                ->whereDoesntHave('profile', fn($q) => $q->whereNotNull('student_number'))
                ->get();

            foreach ($students as $student) {
                $studentNumber = IdGenerator::generateStudentId($defaultDept->id);
                $assignments[] = [
                    'user_id' => $student->id,
                    'number' => $studentNumber,
                    'type' => 'student',
                    'name' => $student->name,
                ];
            }
        }

        // Get staff without employee_number in profile
        if (!$type || $type === 'staff') {
            $staff = User::role(['academic_staff', 'non_academic_staff'])
                ->whereDoesntHave('profile', fn($q) => $q->whereNotNull('employee_number'))
                ->get();

            foreach ($staff as $member) {
                $employeeNumber = IdGenerator::generateEmployeeId($defaultDept->id);
                $assignments[] = [
                    'user_id' => $member->id,
                    'number' => $employeeNumber,
                    'type' => 'staff',
                    'name' => $member->name,
                ];
            }
        }

        if (empty($assignments)) {
            $this->info('All users already have numbers assigned.');
            return Command::SUCCESS;
        }

        // Display assignments
        foreach ($assignments as $assignment) {
            $this->warn("{$assignment['type']}: {$assignment['name']} -> {$assignment['number']}");
        }

        if ($dryRun) {
            $this->info('[DRY-RUN] No changes made.');
            return Command::SUCCESS;
        }

        // Apply assignments
        foreach ($assignments as $assignment) {
            $user = User::find($assignment['user_id']);

            $user->profile()->create([
                $assignment['type'] . '_number' => $assignment['number'],
            ]);
        }

        $this->info('Assigned numbers to ' . count($assignments) . ' users.');
        return Command::SUCCESS;
    }
}