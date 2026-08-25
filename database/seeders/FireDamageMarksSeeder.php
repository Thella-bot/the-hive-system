<?php

namespace Database\Seeders;

use App\Models\Gradable;
use App\Models\Module;
use App\Models\StudentGrade;
use App\Models\User;
use Illuminate\Database\Seeder;

class FireDamageMarksSeeder extends Seeder
{
    /**
     * Student numbers affected by the fire (graduating students).
     */
    private const AFFECTED_STUDENTS = [
        '20230401',
        '20240401',
        '20230402',
        '20240402',
    ];

    /**
     * All modules from the Diploma in Professional Chef programme (Year 1-3).
     */
    private const ALL_MODULE_CODES = [
        // Year 1 Semester 1
        'HM101', 'HM102', 'HM103', 'HM104', 'HM105', 'HM106', 'HM107', 'HM109', 'HM110', 'HM111', 'HM112', 'HM113',
        'FS101', 'FS102',
        'CG101', 'CG102', 'CG103', 'CG104',
        'PT101',
        'GC101',
        // Year 1 Semester 2
        'HM108',
        'PT102', 'PT103', 'PT104', 'PT105', 'PT106',
        'FS103', 'FS104',
        'GC102', 'GC103', 'GC104', 'GC105', 'GC106', 'GC107', 'GC108', 'GC109', 'GC110', 'GC111',
        // Year 2 Semester 1
        'HM202',
        'FS201', 'FS202',
        'GC201', 'GC202', 'GC203', 'GC204', 'GC205', 'GC206', 'GC207', 'GC208', 'GC209',
        'PT201', 'PT202', 'PT203', 'PT204', 'PT205',
        'CG201',
        // Year 3 Semester 1
        'HM203', 'HM204', 'HM205', 'HM206', 'HM207', 'HM208', 'HM209', 'HM210',
        'FS301', 'FS302',
        'GC301', 'GC302', 'GC303', 'GC304', 'GC305',
        'CG301', 'CG302',
    ];

    /**
     * Gradable types to create per module.
     */
    private const GRADABLE_TEMPLATES = [
        ['title' => 'Final Examination', 'type' => 'final_exam', 'max_marks' => 100, 'weight' => 40],
        ['title' => 'Mid-Term Test', 'type' => 'mid-term_exam', 'max_marks' => 100, 'weight' => 30],
        ['title' => 'Coursework Assignment', 'type' => 'assignment', 'max_marks' => 50, 'weight' => 20],
        ['title' => 'Practical Quiz', 'type' => 'quiz', 'max_marks' => 30, 'weight' => 10],
    ];

    public function run(): void
    {
        $students = User::whereIn('student_number', self::AFFECTED_STUDENTS)->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found with the specified student numbers.');
            return;
        }

        $modules = Module::whereIn('code', self::ALL_MODULE_CODES)->get();

        if ($modules->isEmpty()) {
            $this->command->warn('No modules found. Please run module seeding first.');
            return;
        }

        $this->command->info("Found {$students->count()} affected students and {$modules->count()} modules.");

        // Create gradables for modules that don't have them
        $gradableCount = $this->ensureGradables($modules);
        $this->command->info("Created {$gradableCount} new gradables for modules.");

        // Enroll students in all modules with appropriate academic year/semester
        $enrollmentCount = $this->enrollStudents($students, $modules);
        $this->command->info("Created {$enrollmentCount} enrollments.");

        // Generate marks
        $totalGrades = $this->generateMarks($students);
        $this->command->info("Generated {$totalGrades} student grade records.");
    }

    /**
     * Ensure each module has gradable items.
     */
    private function ensureGradables($modules): int
    {
        $count = 0;

        foreach ($modules as $module) {
            if ($module->gradables()->count() > 0) {
                continue;
            }

            $instructorId = $module->instructors()->first()?->id;

            foreach (self::GRADABLE_TEMPLATES as $template) {
                Gradable::create([
                    'title' => $template['title'],
                    'type' => $template['type'],
                    'submission_type' => 'file_upload',
                    'module_id' => $module->id,
                    'instructor_id' => $instructorId,
                    'description' => "{$template['title']} for {$module->name}",
                    'due_date' => now()->subDays(random_int(30, 365)),
                    'max_marks' => $template['max_marks'],
                    'weight' => $template['weight'],
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Enroll students in all modules with correct academic year and semester.
     */
    private function enrollStudents($students, $modules): int
    {
        $count = 0;

        foreach ($students as $student) {
            foreach ($modules as $module) {
                $academicYear = $this->getAcademicYearForModule($module->code);
                $semester = $this->getSemesterForModule($module->code);

                $enrollment = $student->enrollments()->firstOrCreate([
                    'module_id' => $module->id,
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                ]);

                if ($enrollment->wasRecentlyCreated) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get the academic year for a module based on its code.
     */
    private function getAcademicYearForModule(string $code): string
    {
        $year = substr($code, 2, 1);

        return match ($year) {
            '1' => '2023',
            '2' => '2024',
            '3' => '2025',
            default => '2025',
        };
    }

    /**
     * Get the semester for a module based on its code.
     */
    private function getSemesterForModule(string $code): string
    {
        $year1Semester2 = ['HM108', 'PT102', 'PT103', 'PT104', 'PT105', 'PT106', 'FS103', 'FS104', 'GC102', 'GC103', 'GC104', 'GC105', 'GC106', 'GC107', 'GC108', 'GC109', 'GC110', 'GC111'];

        if (in_array($code, $year1Semester2)) {
            return '2';
        }

        return '1';
    }

    /**
     * Generate randomized marks for all gradables.
     */
    private function generateMarks($students): int
    {
        $totalGrades = 0;

        foreach ($students as $student) {
            $enrollments = $student->enrollments()->with('module.gradables')->get();

            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->module->gradables as $gradable) {
                    $maxMarks = $gradable->max_marks ?? 100;
                    $mark = $this->generateRealisticMark($maxMarks);

                    StudentGrade::updateOrCreate(
                        [
                            'gradable_id' => $gradable->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'marks' => $mark,
                        ]
                    );

                    $totalGrades++;
                }
            }
        }

        return $totalGrades;
    }

    /**
     * Generate a realistic mark between 40% and 95% of max marks.
     */
    private function generateRealisticMark(int $maxMarks): float
    {
        $percentage = match (random_int(1, 10)) {
            1, 2 => random_int(40, 54),   // 20% chance: borderline pass
            3, 4, 5 => random_int(55, 69), // 30% chance: average
            6, 7, 8 => random_int(70, 84), // 30% chance: good
            default => random_int(85, 95),  // 20% chance: excellent
        };

        return round(($percentage / 100) * $maxMarks, 2);
    }
}
