<?php

namespace Database\Seeders;

use App\Models\Gradable;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class FireDamageSubmissionsSeeder extends Seeder
{
    /**
     * Student numbers affected by the fire (graduating students).
     */
    private const AFFECTED_STUDENTS = [
        'S20230401',
        'S20240401',
        'S20230402',
        'S20240402',
    ];

    public function run(): void
    {
        $students = User::whereIn('student_number', self::AFFECTED_STUDENTS)->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found with the specified student numbers.');
            return;
        }

        $this->command->info("Found {$students->count()} affected students. Creating submissions...");

        $totalSubmissions = 0;

        foreach ($students as $student) {
            $enrollments = $student->enrollments()->with('module.gradables')->get();

            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->module->gradables as $gradable) {
                    $maxMarks = $gradable->max_marks ?? 100;
                    $grade = $this->generateRealisticMark($maxMarks);

                    Submission::updateOrCreate(
                        [
                            'gradable_id' => $gradable->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'file_path' => 'private/submissions/' . \Str::uuid() . '.pdf',
                            'submitted_at' => now()->subDays(random_int(30, 365)),
                            'is_late' => false,
                            'grade' => $grade,
                            'graded_at' => now()->subDays(random_int(1, 30)),
                        ]
                    );

                    $totalSubmissions++;
                }
            }
        }

        $this->command->info("Created {$totalSubmissions} submissions with grades.");
    }

    /**
     * Generate a realistic mark between 80% and 95% of max marks.
     */
    private function generateRealisticMark(int $maxMarks): float
    {
        $percentage = random_int(80, 95);

        return round(($percentage / 100) * $maxMarks, 2);
    }
}
