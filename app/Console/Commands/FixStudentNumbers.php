<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use App\Services\IdGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixStudentNumbers extends Command
{
    protected $signature = 'students:fix-numbers {--dry-run : Show what would be fixed without making changes}';

    protected $description = 'Fix malformed student numbers to use proper format (SYYYYDDNN)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Get all malformed student numbers from both User and Profile
        $users = User::whereNotNull('student_number')
            ->where('student_number', 'not like', 'S%')
            ->get();

        $profiles = Profile::whereNotNull('student_number')
            ->where('student_number', 'not like', 'S%')
            ->get();

        if ($users->isEmpty() && $profiles->isEmpty()) {
            $this->info('No malformed student numbers found.');
            return Command::SUCCESS;
        }

        $fixes = [];

        foreach ($users as $user) {
            $fixes[] = [
                'type' => 'user',
                'id' => $user->id,
                'old' => $user->student_number,
                'new' => null,
            ];
        }

        foreach ($profiles as $profile) {
            $fixes[] = [
                'type' => 'profile',
                'id' => $profile->id,
                'old' => $profile->student_number,
                'new' => null,
            ];
        }

        // Assign properly formatted IDs using IdGenerator
        foreach ($fixes as &$fix) {
            $user = $fix['type'] === 'user' ? User::find($fix['id']) : null;
            $profile = $fix['type'] === 'profile' ? Profile::find($fix['id']) : null;

            $departmentId = $profile?->department_id ?? 0;
            $fix['new'] = IdGenerator::generateStudentId($departmentId);
        }
        unset($fix);

        // Display fixes
        foreach ($fixes as $fix) {
            $this->warn("{$fix['type']} {$fix['id']} ({$fix['old']}) -> {$fix['new']}");
        }

        if ($dryRun) {
            $this->info('[DRY-RUN] No changes made.');
            return Command::SUCCESS;
        }

        // Apply fixes in a transaction
        DB::transaction(function () use ($fixes) {
            foreach ($fixes as $fix) {
                if ($fix['type'] === 'user') {
                    User::where('id', $fix['id'])->update(['student_number' => $fix['new']]);
                } else {
                    Profile::where('id', $fix['id'])->update(['student_number' => $fix['new']]);
                }
            }
        });

        $this->info('Fixed ' . count($fixes) . ' student numbers.');
        return Command::SUCCESS;
    }
}