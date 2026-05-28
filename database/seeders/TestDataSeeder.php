<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Module;
use App\Models\Cohort;
use App\Models\AcademicYear;
use App\Models\Application;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Document;
use App\Models\Gradable;
use App\Models\Submission;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\Programme;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAcademicYears();
        $this->seedCohorts();
        $this->seedModules();
        $this->seedStudents();
        $this->seedApplications();
        $this->seedAnnouncements();
        $this->seedEvents();
        $this->seedDocuments();
        $this->seedGradables();

        // These may fail due to missing tables/listeners - wrap gracefully
        try {
            $this->seedSubmissions();
        } catch (\Throwable $e) {
            $this->command->warn('Submissions seeding skipped: ' . $e->getMessage());
        }

        try {
            $this->seedLeaveRequests();
        } catch (\Throwable $e) {
            $this->command->warn('Leave requests seeding skipped: ' . $e->getMessage());
        }

        try {
            $this->seedPayslips();
        } catch (\Throwable $e) {
            $this->command->warn('Payslips seeding skipped: ' . $e->getMessage());
        }
    }

    private function seedAcademicYears(): void
    {
        $years = [
            ['name' => '2024 Academic Year', 'start_date' => '2024-01-15', 'end_date' => '2024-12-15', 'is_current' => false],
            ['name' => '2025 Academic Year', 'start_date' => '2025-01-15', 'end_date' => '2025-12-15', 'is_current' => false],
            ['name' => '2026 Academic Year', 'start_date' => '2026-01-15', 'end_date' => '2026-12-15', 'is_current' => true],
        ];

        foreach ($years as $year) {
            AcademicYear::firstOrCreate(['name' => $year['name']], $year);
        }

        $this->command->info('Academic years seeded.');
    }

    private function seedCohorts(): void
    {
        $academicYear = AcademicYear::where('name', 'like', '%2026%')->first();
        $department = Department::first();
        if (!$academicYear || !$department) return;

        $cohorts = [
            ['name' => 'Cohort A - January 2026', 'slug' => 'cohort-a-jan-2026', 'department_id' => $department->id, 'academic_year_id' => $academicYear->id],
            ['name' => 'Cohort B - April 2026', 'slug' => 'cohort-b-apr-2026', 'department_id' => $department->id, 'academic_year_id' => $academicYear->id],
            ['name' => 'Cohort C - July 2026', 'slug' => 'cohort-c-jul-2026', 'department_id' => $department->id, 'academic_year_id' => $academicYear->id],
        ];

        foreach ($cohorts as $cohort) {
            Cohort::firstOrCreate(['slug' => $cohort['slug']], $cohort);
        }

        $this->command->info('Cohorts seeded.');
    }

    private function seedModules(): void
    {
        $department = Department::first();
        $programme = Programme::first();
        if (!$department || !$programme) return;

        $modules = [
            ['name' => 'Introduction to Culinary Arts', 'code' => 'CUL101', 'description' => 'Fundamentals of cooking techniques and kitchen safety', 'department_id' => $department->id, 'programme_id' => $programme->id],
            ['name' => 'Food Safety & Hygiene', 'code' => 'CUL102', 'description' => 'HACCP principles and food safety regulations', 'department_id' => $department->id, 'programme_id' => $programme->id],
            ['name' => 'Baking & Patisserie Basics', 'code' => 'CUL103', 'description' => 'Introduction to baking, pastries, and confectionery', 'department_id' => $department->id, 'programme_id' => $programme->id],
            ['name' => 'Global Cuisines', 'code' => 'CUL104', 'description' => 'Exploring culinary traditions from around the world', 'department_id' => $department->id, 'programme_id' => $programme->id],
            ['name' => 'Kitchen Operations', 'code' => 'CUL105', 'description' => 'Commercial kitchen management and operations', 'department_id' => $department->id, 'programme_id' => $programme->id],
            ['name' => 'Nutrition & Menu Planning', 'code' => 'CUL106', 'description' => 'Understanding nutrition and designing balanced menus', 'department_id' => $department->id, 'programme_id' => $programme->id],
        ];

        foreach ($modules as $module) {
            Module::firstOrCreate(['code' => $module['code']], $module);
        }

        $this->command->info('Modules seeded.');
    }

    private function seedStudents(): void
    {
        $programme = Programme::first();
        $modules = $programme ? $programme->modules()->pluck('id') : collect();

        // Fully registered students (student role + modules assigned)
        $registeredStudents = [
            ['name' => 'Thabo Mokoena', 'email' => 'thabo.mokoena@student.hbci.ac.ls', 'student_number' => 'HBCI-2026-001'],
            ['name' => 'Lerato Moloi', 'email' => 'lerato.moloi@student.hbci.ac.ls', 'student_number' => 'HBCI-2026-002'],
            ['name' => 'Kemoetse Rantso', 'email' => 'kemoetse.rantso@student.hbci.ac.ls', 'student_number' => 'HBCI-2026-003'],
            ['name' => 'Mamo Moshoeshoe', 'email' => 'mamo.moshoeshoe@student.hbci.ac.ls', 'student_number' => 'HBCI-2026-004'],
            ['name' => 'Ntshepang Mahlako', 'email' => 'ntshepang.mahlako@student.hbci.ac.ls', 'student_number' => 'HBCI-2026-005'],
            // Also link Limpho Sello (fully registered in seedApplications)
            ['name' => 'Limpho Sello', 'email' => 'limpho.sello@email.com', 'student_number' => 'HBCI-2026-006'],
        ];

        foreach ($registeredStudents as $studentData) {
            $user = User::firstOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->student_number = $studentData['student_number'] ?? null;
            if ($programme) {
                $user->programme_id = $programme->id;
            }
            $user->save();
            $user->assignRole('student');

            // Sync modules for fully registered students
            if ($modules->isNotEmpty()) {
                $user->modules()->sync($modules);
            }
        }

        // Admitted but not registered yet (student role but no modules)
        $admittedNotRegistered = [
            ['name' => 'Pitso Mofokeng', 'email' => 'pitso.mofokeng@email.com'],
        ];

        foreach ($admittedNotRegistered as $studentData) {
            $user = User::firstOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            if ($programme) {
                $user->programme_id = $programme->id;
            }
            $user->save();
            $user->assignRole('student');
            // Note: NO modules synced - user needs to complete registration first
        }

        $this->command->info('Students seeded.');
    }

    private function seedApplications(): void
    {
        $programme = Programme::first();
        if (!$programme) return;

        $applications = [
            // Pending applications (not yet admitted) - omit admitted_at/registration_status to use defaults
            ['name' => 'Refiloe Nkosi', 'email' => 'refiloe.nkosi@email.com', 'phone' => '+266 5012 3456', 'status' => 'pending'],
            ['name' => 'Matseliso Thaba', 'email' => 'matseliso.thaba@email.com', 'phone' => '+266 5023 4567', 'status' => 'pending'],
            ['name' => 'Moshe Morule', 'email' => 'moshe.morule@email.com', 'phone' => '+266 5056 7890', 'status' => 'pending'],
            // Admitted but not registered yet (can log in, see registration page)
            ['name' => 'Pitso Mofokeng', 'email' => 'pitso.mofokeng@email.com', 'phone' => '+266 5034 5678', 'status' => 'approved', 'admitted_at' => now(), 'registration_status' => 'pending'],
            // Admitted and fully registered (full access)
            ['name' => 'Limpho Sello', 'email' => 'limpho.sello@email.com', 'phone' => '+266 5045 6789', 'status' => 'approved', 'admitted_at' => now()->subDays(3), 'registration_status' => 'completed', 'registered_at' => now()->subDays(2), 'payment_verified_at' => now()->subDays(2)],
        ];

        foreach ($applications as $app) {
            $record = Application::updateOrCreate(
                ['email' => $app['email']],
                array_merge($app, [
                    'programme_id' => $programme->id,
                ])
            );
            if (! $record->created_at) {
                $record->created_at = now();
                $record->save();
            }
        }

        $this->command->info('Applications seeded.');
    }

    private function seedAnnouncements(): void
    {
        $admin = User::role('school-admin')->first() ?? User::first();

        $announcements = [
            [
                'title' => 'Welcome to 2026 Academic Year',
                'body' => 'We are excited to welcome all new and returning students to the 2026 academic year. Remember to check your module schedules and report any issues to the administration.',
                'category' => 'general',
                'created_by' => $admin?->id,
            ],
            [
                'title' => 'Kitchen Practical Exam Schedule',
                'body' => 'The schedule for upcoming kitchen practical exams has been posted. Please check the module pages for specific dates and requirements.',
                'category' => 'academic',
                'created_by' => $admin?->id,
            ],
            [
                'title' => 'Campus Food Safety Training',
                'body' => 'Mandatory food safety training for all first-year students will be held on the 15th. Attendance is required.',
                'category' => 'event',
                'created_by' => $admin?->id,
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::firstOrCreate(
                ['title' => $announcement['title']],
                $announcement
            );
        }

        $this->command->info('Announcements seeded.');
    }

    private function seedEvents(): void
    {
        $events = [
            [
                'title' => 'Culinary Competition 2026',
                'description' => 'Annual inter-cohort culinary competition. Show off your skills!',
                'start' => Carbon::now()->addDays(14)->setHour(9),
                'end' => Carbon::now()->addDays(14)->setHour(17),
            ],
            [
                'title' => 'Industry Guest Chef Workshop',
                'description' => 'Renowned chef from Switzerland will conduct a masterclass on molecular gastronomy.',
                'start' => Carbon::now()->addDays(7)->setHour(10),
                'end' => Carbon::now()->addDays(7)->setHour(14),
            ],
            [
                'title' => 'Graduation Ceremony',
                'description' => 'Class of 2025 graduation ceremony.',
                'start' => Carbon::now()->addMonth(2)->setHour(10),
                'end' => Carbon::now()->addMonth(2)->setHour(16),
            ],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate(
                ['title' => $event['title']],
                $event
            );
        }

        $this->command->info('Events seeded.');
    }

    private function seedDocuments(): void
    {
        $module = Module::first();
        $admin = User::role('school-admin')->first() ?? User::first();
        if (!$module || !$admin) return;

        $documents = [
            ['title' => 'Kitchen Safety Manual', 'category' => 'Book', 'module_id' => $module->id, 'created_by' => $admin->id],
            ['title' => 'Week 1 Lecture Notes - Culinary Basics', 'category' => 'Notes', 'module_id' => $module->id, 'created_by' => $admin->id],
            ['title' => 'January 2026 Class Schedule', 'category' => 'Schedule', 'module_id' => null, 'created_by' => $admin->id],
            ['title' => 'Classic French Recipes - Volume 1', 'category' => 'Recipes', 'module_id' => $module->id, 'created_by' => $admin->id],
        ];

        foreach ($documents as $doc) {
            Document::firstOrCreate(
                ['title' => $doc['title']],
                $doc
            );
        }

        $this->command->info('Documents seeded.');
    }

    private function seedGradables(): void
    {
        $module = Module::first();
        if (!$module) return;

        $gradables = [
            ['title' => 'Kitchen Safety Quiz', 'type' => 'quiz', 'module_id' => $module->id, 'max_marks' => 20, 'due_date' => Carbon::now()->addDays(7)],
            ['title' => 'Cooking Test - Basic Techniques', 'type' => 'test', 'module_id' => $module->id, 'max_marks' => 50, 'due_date' => Carbon::now()->addDays(14)],
            ['title' => 'Menu Planning Assignment', 'type' => 'assignment', 'module_id' => $module->id, 'max_marks' => 30, 'due_date' => Carbon::now()->addDays(21)],
            ['title' => 'Mid-Term Examination', 'type' => 'mid-term_exam', 'module_id' => $module->id, 'max_marks' => 100, 'due_date' => Carbon::now()->addDays(30)],
        ];

        foreach ($gradables as $gradable) {
            Gradable::firstOrCreate(
                ['title' => $gradable['title'], 'module_id' => $gradable['module_id']],
                $gradable
            );
        }

        $this->command->info('Gradables (assessments) seeded.');
    }

    private function seedSubmissions(): void
    {
        $student = User::role('student')->first();
        $gradable = Gradable::first();
        if (!$student || !$gradable) return;

        Submission::firstOrCreate(
            ['student_id' => $student->id, 'gradable_id' => $gradable->id],
            [
                'submitted_at' => Carbon::now()->subDays(2),
                'file_path' => 'submissions/sample_submission.pdf',
            ]
        );

        $this->command->info('Submissions seeded.');
    }

    private function seedLeaveRequests(): void
    {
        $staff = User::role('academic_staff')->first();
        if (!$staff) return;

        $leaves = [
            ['type' => 'annual', 'start_date' => Carbon::now()->addDays(10), 'end_date' => Carbon::now()->addDays(15), 'reason' => 'Family vacation', 'status' => 'pending'],
            ['type' => 'sick', 'start_date' => Carbon::now()->subDays(5), 'end_date' => Carbon::now()->subDays(4), 'reason' => 'Doctor appointment', 'status' => 'approved'],
        ];

        foreach ($leaves as $leave) {
            LeaveRequest::firstOrCreate(
                ['user_id' => $staff->id, 'start_date' => $leave['start_date']],
                $leave
            );
        }

        $this->command->info('Leave requests seeded.');
    }

    private function seedPayslips(): void
    {
        $staff = User::role('academic_staff')->first();
        if (!$staff) return;

        $grossSalary = 19500;
        $deductions = 500;
        $netSalary = $grossSalary - $deductions;

        $payslips = [
            [
                'user_id' => $staff->id,
                'pay_period_start' => Carbon::now()->startOfMonth(),
                'pay_period_end' => Carbon::now()->endOfMonth(),
                'gross_salary' => $grossSalary,
                'deductions' => $deductions,
                'net_salary' => $netSalary,
                'earnings' => json_encode(['basic_salary' => 15000, 'housing_allowance' => 3000, 'transport_allowance' => 1500]),
                'deductions_breakdown' => json_encode(['tax' => 500]),
                'leave_deducted' => 0,
                'leave_days_taken' => 0,
            ],
        ];

        foreach ($payslips as $payslip) {
            Payslip::firstOrCreate(
                ['user_id' => $payslip['user_id'], 'pay_period_start' => $payslip['pay_period_start']],
                $payslip
            );
        }

        $this->command->info('Payslips seeded.');
    }
}
