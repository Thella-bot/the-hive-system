<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Programme;
use App\Models\User;
use App\Services\StudentModuleAssignmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStudentModules extends Command
{
    protected $signature = 'students:sync-modules
                            {--dry-run : Report what would change without writing anything}
                            {--student= : Limit to a single student id or student number}
                            {--detach : Remove modules that are not on the student\'s programme (default)}';

    protected $description = 'Give every student exactly the modules on their programme';

    public function handle(StudentModuleAssignmentService $assignments): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $only = $this->option('student');

        $students = User::query()
            ->students()
            ->when($only, fn ($query) => $query->where(
                fn ($q) => $q->where('id', $only)->orWhere('student_number', $only)
            ))
            ->with('programme')
            ->orderBy('student_number')
            ->get();

        if ($students->isEmpty()) {
            $this->error('No students matched.');

            return Command::FAILURE;
        }

        $programmes = Programme::pluck('name', 'id');
        $rows = [];
        $changed = 0;

        foreach ($students as $student) {
            $report = $assignments->inspect($student, $student->programme);

            if ($report['in_sync']) {
                continue;
            }

            $changed++;

            $rows[] = [
                $student->student_number ?? $student->profile?->student_number ?? '-',
                $student->name,
                $programmes[$student->programme_id] ?? 'NO PROGRAMME',
                $report['expected'],
                $report['actual'],
                $report['added'] === [] ? '-' : count($report['added']),
                $report['removed'] === [] ? '-' : count($report['removed']),
            ];
        }

        $this->info(sprintf('Checked %d students, %d need changes.', $students->count(), $changed));

        if ($rows !== []) {
            $this->table(
                ['Number', 'Student', 'Programme', 'Expected', 'Current', 'Adding', 'Removing'],
                $rows,
            );
        }

        if ($changed === 0) {
            $this->info('Every student already matches their programme.');

            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->info('[DRY-RUN] Nothing was written.');

            return Command::SUCCESS;
        }

        DB::transaction(function () use ($students, $assignments) {
            foreach ($students as $student) {
                $student->loadMissing('programme');
                $assignments->sync($student, $student->programme);
            }
        });

        $this->info(sprintf('Synced %d students.', $changed));

        return Command::SUCCESS;
    }
}
