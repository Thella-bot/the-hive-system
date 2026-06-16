<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

/**
 * Role-Based Access Control (RBAC) Seeder for The Hive
 *
 * Defines 21 roles covering all functional domains:
 * - Super Admin: Full system configuration
 * - Academic Director: Curriculum, faculty, accreditation
 * - Program Coordinator: Timetables, batches, allocation
 * - Chef Instructor: Kitchen classes, recipes, assessments
 * - Pastry/Bakery Instructor: Labs, patisserie inventory
 * - Sous Chef (TA): Kitchen prep, grading assistance
 * - Student: Enrolled learner
 * - Admissions Officer: Enquiries, applications, enrollment
 * - Examination Cell: Exams, grades, certifications
 * - Registrar: Records, transcripts, graduation
 * - Finance/Accounts: Fees, payroll, expenses
 * - Procurement Manager: Suppliers, POs, restocking
 * - Storekeeper: Inventory, equipment, issuing
 * - HR Manager: Staff records, leave, recruitment
 * - Librarian: Books, resources, issue/return
 * - Career Services: Placements, alumni network
 * - Events & PR Manager: Events, demos, media
 * - Cafeteria/Bistro Manager: POS, menu, sales
 * - IT Support/System Admin: Intranet, devices, help desk
 * - Parent/Guardian: Limited academic view
 * - Alumni: Network portal, job board
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- 21 Roles ---
        $roles = [
            'super-admin',               // Full system configuration, security, data governance
            'academic-director',       // Curriculum, faculty management, accreditation
            'program-coordinator',     // Timetables, student batches, course allocation
            'chef-instructor',         // Recipes, kitchen classes, ingredient requisitions, assessments
            'pastry-instructor',       // Specialized labs, patisserie inventory
            'sous-chef',              // Kitchen prep supervision, grading assistance
            'student',                // Class schedules, assignments, recipes, locker, fee status
            'admissions-officer',     // Enquiries, applications, enrollment, interviews
            'examination-cell',     // Exam scheduling, grade processing, certifications
            'registrar',              // Student records, transcripts, graduation clearance
            'finance',                // Fees, payroll, vendor payments, budgeting
            'procurement-manager',    // Supplier management, purchase orders, inventory restocking
            'storekeeper',            // Ingredient stock, equipment, issuing to kitchens
            'hr-manager',            // Staff records, leave, recruitment, payroll inputs
            'librarian',             // Physical & digital culinary resources, book issue
            'career-services',       // Internships, placements, industry tie-ups, alumni
            'events-pr-manager',    // Institute events, guest chef demos, media
            'cafeteria-manager',     // Student-run restaurant POS, menu, sales tracking
            'it-support',           // Intranet uptime, device management, help desk
            'parent-guardian',      // Academic progress, fee status, attendance (limited view)
            'alumni',               // Network portal, job board, institute updates
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }


        // --- Domain-Specific Permissions ---
        $permissions = [
            // === Admissions & Enrollment Module ===
            'view-inquiries', 'manage-inquiries',
            'view-applications', 'create-applications', 'edit-applications', 'delete-applications',
            'view-enrollments', 'create-enrollments', 'edit-enrollments',

            // === Student Information System (SIS) ===
            'view-students', 'create-students', 'edit-students', 'delete-students',
            'view-student-profile', 'edit-student-profile',
            'view-student-attendance', 'manage-student-attendance',
            'view-disciplinary-records', 'manage-disciplinary-records',

            // === Academic & Curriculum ===
            'view-programmes', 'create-programmes', 'edit-programmes', 'delete-programmes',
            'view-modules', 'create-modules', 'edit-modules', 'delete-modules',
            'view-lessons', 'create-lessons', 'edit-lessons', 'delete-lessons',
            'view-timetables', 'create-timetables', 'edit-timetables',
            'view-faculty', 'manage-faculty',

            // === Kitchen & Lab Operations ===
            'view-recipes', 'create-recipes', 'edit-recipes', 'delete-recipes',
            'view-kitchen-stations', 'assign-kitchen-stations',
            'view-mise-en-place', 'manage-mise-en-place',
            'view-ingredient-requisitions', 'create-ingredient-requisitions', 'approve-requisitions',

            // === Inventory & Procurement ===
            'view-inventory', 'manage-inventory',
            'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers',
            'view-purchase-orders', 'create-purchase-orders', 'approve-purchase-orders',
            'view-requisitions', 'create-requisitions', 'process-requisitions',
            'view-uniforms', 'manage-uniforms',

            // === Finance & Billing ===
            'view-fees', 'create-fees', 'edit-fees',
            'view-invoices', 'create-invoices', 'manage-invoices',
            'view-payments', 'record-payments',
            'view-payroll', 'manage-payroll',
            'view-expenses', 'create-expenses', 'manage-expenses',
            'view-budgets', 'manage-budgets',

            // === Examination & Assessment ===
            'view-exams', 'create-exams', 'edit-exams', 'manage-exams',
            'view-grades', 'manage-grades',
            'view-assessments', 'create-assessments', 'grade-assessments',
            'view-certifications', 'issue-certifications',

            // === Library ===
            'view-library', 'manage-library',
            'view-books', 'manage-books',
            'view-digital-resources', 'manage-digital-resources',
            'issue-books', 'return-books',

            // === Student-Run Restaurant/Bistro ===
            'view-bistro', 'manage-bistro',
            'view-menu', 'manage-menu',
            'view-sales', 'manage-sales',
            'view-reservations', 'manage-reservations',

            // === Career Services ===
            'view-internships', 'post-internships', 'manage-internships',
            'view-placements', 'manage-placements',
            'view-alumni', 'manage-alumni',
            'view-job-board', 'manage-job-board',

            // === HR & Staff ===
            'view-staff', 'create-staff', 'edit-staff', 'delete-staff',
            'view-leave-requests', 'create-leave-requests', 'approve-leave-requests',
            'view-attendance-staff', 'manage-attendance-staff',
            'view-recruitment', 'manage-recruitment',
            'view-training', 'manage-training',
            'view-appraisals', 'manage-appraisals',

            // === Events & Masterclasses ===
            'view-events', 'create-events', 'edit-events', 'delete-events',
            'view-event-registrations', 'manage-event-registrations',
            'view-media', 'manage-media',

            // === Hygiene, Safety & Compliance ===
            'view-safety-logs', 'create-safety-logs', 'manage-safety-logs',
            'view-incidents', 'report-incidents',
            'view-haccp', 'manage-haccp',
            'view-compliance', 'manage-compliance',

            // === Intranet Core ===
            'view-announcements', 'create-announcements', 'edit-announcements', 'delete-announcements',
            'view-posts', 'create-posts', 'edit-posts', 'delete-posts',
            'view-messages', 'send-messages',
            'view-groups', 'manage-groups',
            'view-documents', 'manage-documents',

            // === Analytics & Reporting ===
            'view-reports', 'generate-reports',
            'view-dashboards', 'manage-dashboards',
            'export-data',

            // === System Administration ===
            'manage-settings', 'manage-roles', 'manage-users',
            'view-audit-logs', 'manage-audit-logs',
            'manage-api-keys',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- Role-Permission Assignments ---

        // Super Admin: Full system access
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Academic Director: Curriculum, faculty, accreditation
        $academicDirector = Role::firstOrCreate(['name' => 'academic-director']);
        $academicDirector->givePermissionTo([
            'view-programmes', 'create-programmes', 'edit-programmes',
            'view-modules', 'create-modules', 'edit-modules', 'delete-modules',
            'view-faculty', 'manage-faculty',
            'view-lessons', 'create-lessons', 'edit-lessons', 'delete-lessons',
            'view-timetables', 'create-timetables', 'edit-timetables',
            'view-recipes', 'create-recipes', 'edit-recipes', 'delete-recipes',
            'view-kitchen-stations', 'assign-kitchen-stations',
            'view-mise-en-place', 'manage-mise-en-place',
            'view-events', 'create-events', 'edit-events',
            'view-reports', 'generate-reports',
            'view-compliance', 'manage-compliance',
            'view-announcements', 'create-announcements', 'edit-announcements',
            'view-exams', 'create-exams', 'edit-exams',
            'view-grades', 'manage-grades',
            'view-certifications', 'issue-certifications',
            'view-students', 'view-student-profile',
            'view-student-attendance',
            'manage-accreditation',
        ]);

        // Program Coordinator: Timetables, batches, allocation
        $programCoordinator = Role::firstOrCreate(['name' => 'program-coordinator']);
        $programCoordinator->givePermissionTo([
            'view-programmes', 'edit-programmes',
            'view-modules', 'edit-modules',
            'view-timetables', 'create-timetables', 'edit-timetables',
            'view-cohorts', 'create-cohorts', 'edit-cohorts',
            'view-students', 'create-students', 'edit-students',
            'view-student-attendance', 'manage-student-attendance',
            'view-faculty',
            'view-lessons', 'create-lessons', 'edit-lessons',
            'view-kitchen-stations', 'assign-kitchen-stations',
            'view-reports', 'generate-reports',
            'view-announcements', 'create-announcements',
            'view-events',
        ]);

        // Chef Instructor: Recipes, kitchen classes, assessments
        $chefInstructor = Role::firstOrCreate(['name' => 'chef-instructor']);
        $chefInstructor->givePermissionTo([
            'view-modules', 'edit-modules',
            'view-recipes', 'create-recipes', 'edit-recipes', 'delete-recipes',
            'view-kitchen-stations', 'assign-kitchen-stations',
            'view-mise-en-place', 'manage-mise-en-place',
            'view-ingredient-requisitions', 'create-ingredient-requisitions',
            'view-students', 'view-student-profile',
            'view-student-attendance', 'manage-student-attendance',
            'view-grades', 'manage-grades',
            'view-assessments', 'create-assessments', 'grade-assessments',
            'view-lessons', 'create-lessons', 'edit-lessons',
            'view-announcements', 'create-announcements',
            'view-posts', 'create-posts',
            'view-library',
            'issue-books',
        ]);

        // Pastry/Bakery Instructor
        $pastryInstructor = Role::firstOrCreate(['name' => 'pastry-instructor']);
        $pastryInstructor->givePermissionTo($chefInstructor->permissions->pluck('name')->toArray());

        // Sous Chef (Teaching Assistant)
        $sousChef = Role::firstOrCreate(['name' => 'sous-chef']);
        $sousChef->givePermissionTo([
            'view-modules',
            'view-recipes', 'view-kitchen-stations', 'assign-kitchen-stations',
            'view-mise-en-place', 'manage-mise-en-place',
            'view-ingredient-requisitions', 'create-ingredient-requisitions',
            'view-students', 'view-student-profile',
            'view-student-attendance', 'manage-student-attendance',
            'view-grades', 'manage-grades',
            'view-assessments', 'grade-assessments',
            'view-lessons',
            'view-announcements',
            'view-posts', 'create-posts',
        ]);

        // Student
        $student = Role::firstOrCreate(['name' => 'student']);
        $student->givePermissionTo([
            'view-programmes', 'view-modules', 'view-lessons',
            'view-recipes', 'view-timetables',
            'view-student-attendance',
            'view-announcements', 'view-posts', 'create-posts',
            'view-library', 'issue-books', 'return-books',
            'view-digital-resources',
            'create-assessments', 'view-grades',
            'view-fees', 'view-invoices', 'view-payments',
            'view-events', 'view-event-registrations',
            'view-internships', 'view-placements', 'view-job-board',
            'view-bistro', 'view-menu', 'view-reservations',
        ]);

        // Admissions Officer
        $admissionsOfficer = Role::firstOrCreate(['name' => 'admissions-officer']);
        $admissionsOfficer->givePermissionTo([
            'view-inquiries', 'manage-inquiries',
            'view-applications', 'create-applications', 'edit-applications',
            'view-enrollments', 'create-enrollments', 'edit-enrollments',
            'view-students', 'create-students',
            'view-programmes',
            'view-announcements', 'create-announcements',
            'view-events', 'create-events', 'edit-events',
            'view-reports', 'generate-reports',
            'view-certifications',
        ]);

        // Examination Cell
        $examinationCell = Role::firstOrCreate(['name' => 'examination-cell']);
        $examinationCell->givePermissionTo([
            'view-exams', 'create-exams', 'edit-exams', 'manage-exams',
            'view-students', 'view-student-profile',
            'view-grades', 'manage-grades',
            'view-assessments', 'create-assessments', 'grade-assessments',
            'view-certifications', 'issue-certifications',
            'view-reports', 'generate-reports', 'export-data',
            'view-announcements', 'create-announcements',
        ]);

        // Registrar
        $registrar = Role::firstOrCreate(['name' => 'registrar']);
        $registrar->givePermissionTo([
            'view-students', 'edit-students',
            'view-student-profile', 'edit-student-profile',
            'view-enrollments', 'edit-enrollments',
            'view-programmes', 'view-modules',
            'view-grades', 'view-certifications',
            'view-student-attendance',
            'view-transcripts',
            'view-reports', 'generate-reports', 'export-data',
            'view-announcements',
        ]);

        // Finance/Accounts
        $finance = Role::firstOrCreate(['name' => 'finance']);
        $finance->givePermissionTo([
            'view-fees', 'create-fees', 'edit-fees',
            'view-invoices', 'create-invoices', 'manage-invoices',
            'view-payments', 'record-payments',
            'view-payroll', 'manage-payroll',
            'view-expenses', 'create-expenses', 'manage-expenses',
            'view-budgets', 'manage-budgets',
            'view-students',
            'view-reports', 'generate-reports', 'export-data',
            'view-payslips', 'download-payslips',
            'view-announcements', 'create-announcements',
        ]);

        // Procurement Manager
        $procurementManager = Role::firstOrCreate(['name' => 'procurement-manager']);
        $procurementManager->givePermissionTo([
            'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers',
            'view-purchase-orders', 'create-purchase-orders', 'approve-purchase-orders',
            'view-requisitions', 'process-requisitions',
            'view-inventory', 'manage-inventory',
            'view-expenses', 'create-expenses',
            'view-budgets',
            'view-reports', 'generate-reports',
            'view-announcements', 'create-announcements',
        ]);

        // Storekeeper
        $storekeeper = Role::firstOrCreate(['name' => 'storekeeper']);
        $storekeeper->givePermissionTo([
            'view-inventory', 'manage-inventory',
            'view-requisitions', 'create-requisitions', 'process-requisitions',
            'view-uniforms', 'manage-uniforms',
            'view-suppliers',
            'view-reports', 'generate-reports',
            'view-announcements',
        ]);

        // HR Manager
        $hrManager = Role::firstOrCreate(['name' => 'hr-manager']);
        $hrManager->givePermissionTo([
            'view-staff', 'create-staff', 'edit-staff', 'delete-staff',
            'view-leave-requests', 'create-leave-requests', 'approve-leave-requests',
            'view-attendance-staff', 'manage-attendance-staff',
            'view-recruitment', 'manage-recruitment',
            'view-training', 'manage-training',
            'view-appraisals', 'manage-appraisals',
            'view-payroll', 'manage-payroll',
            'view-payslips', 'create-payslips', 'download-payslips',
            'view-reports', 'generate-reports',
            'view-announcements', 'create-announcements',
        ]);

        // Librarian
        $librarian = Role::firstOrCreate(['name' => 'librarian']);
        $librarian->givePermissionTo([
            'view-library', 'manage-library',
            'view-books', 'manage-books',
            'view-digital-resources', 'manage-digital-resources',
            'issue-books', 'return-books',
            'view-students', 'view-staff',
            'view-reports', 'generate-reports',
            'view-announcements', 'create-announcements',
            'view-events', 'create-events',
        ]);

        // Career Services
        $careerServices = Role::firstOrCreate(['name' => 'career-services']);
        $careerServices->givePermissionTo([
            'view-internships', 'post-internships', 'manage-internships',
            'view-placements', 'manage-placements',
            'view-alumni', 'manage-alumni',
            'view-job-board', 'manage-job-board',
            'view-students',
            'view-reports', 'generate-reports',
            'view-announcements', 'create-announcements',
            'view-events', 'create-events', 'edit-events',
        ]);

        // Events & PR Manager
        $eventsPrManager = Role::firstOrCreate(['name' => 'events-pr-manager']);
        $eventsPrManager->givePermissionTo([
            'view-events', 'create-events', 'edit-events', 'delete-events',
            'view-event-registrations', 'manage-event-registrations',
            'view-media', 'manage-media',
            'view-students',
            'view-reports', 'generate-reports',
            'view-announcements', 'create-announcements', 'edit-announcements', 'delete-announcements',
            'view-posts', 'create-posts', 'edit-posts', 'delete-posts',
        ]);

        // Cafeteria/Bistro Manager
        $cafeteriaManager = Role::firstOrCreate(['name' => 'cafeteria-manager']);
        $cafeteriaManager->givePermissionTo([
            'view-bistro', 'manage-bistro',
            'view-menu', 'manage-menu',
            'view-sales', 'manage-sales',
            'view-reservations', 'manage-reservations',
            'view-inventory', 'manage-inventory',
            'view-requisitions', 'create-requisitions',
            'view-students',
            'view-reports', 'generate-reports',
            'view-announcements', 'create-announcements',
        ]);

        // IT Support/System Admin
        $itSupport = Role::firstOrCreate(['name' => 'it-support']);
        $itSupport->givePermissionTo([
            'manage-settings',
            'view-users', 'manage-users',
            'view-audit-logs',
            'manage-api-keys',
            'manage-roles',
            'view-announcements', 'create-announcements',
            'view-events', 'create-events',
            'view-reports',
            'view-documents', 'manage-documents',
        ]);

        // Parent/Guardian
        $parentGuardian = Role::firstOrCreate(['name' => 'parent-guardian']);
        $parentGuardian->givePermissionTo([
            'view-student-profile',
            'view-student-attendance',
            'view-grades',
            'view-fees', 'view-invoices',
            'view-announcements',
            'view-events',
        ]);

        // Alumni
        $alumni = Role::firstOrCreate(['name' => 'alumni']);
        $alumni->givePermissionTo([
            'view-programmes',
            'view-alumni',
            'view-internships', 'view-placements', 'view-job-board',
            'view-events',
            'view-announcements',
            'view-posts', 'create-posts',
        ]);

        // --- Seed Test Users ---
        $super = User::firstOrCreate(
            ['email' => 'super@hbci.ac.ls'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $super->assignRole('super-admin');
    }
}