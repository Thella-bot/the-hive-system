<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Roles ---
        $roles = [
            'super-admin',
            'school-admin',
            'department-head',
            'academic_staff',
            'non_academic_staff',
            'student',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }


        // --- Permissions ---
        $permissions = [
            // Application management
            'view-applications', 'edit-applications', 'delete-applications',

            // User management
            'view-users', 'create-users', 'edit-users', 'delete-users',

            // Department management
            'view-departments', 'create-departments', 'edit-departments', 'delete-departments',

            // Academic year management
            'view-academic-years', 'create-academic-years', 'edit-academic-years', 'delete-academic-years',

            // Cohort management
            'view-cohorts', 'create-cohorts', 'edit-cohorts', 'delete-cohorts',

            // Student management
            'view-students', 'create-students', 'edit-students', 'delete-students',
            'view-student-grades', 'manage-student-grades',
            'view-student-attendance', 'manage-student-attendance',

            // Staff management
            'view-staff', 'create-staff', 'edit-staff', 'delete-staff',

            // Learning module
            'view-courses', 'create-courses', 'edit-courses', 'delete-courses',
            'view-lessons', 'create-lessons', 'edit-lessons', 'delete-lessons',

            // Engagement module
            'view-announcements', 'create-announcements', 'edit-announcements', 'delete-announcements',
            'view-posts', 'create-posts', 'edit-posts', 'delete-posts',

            // HR
            'view-leave-requests', 'create-leave-requests', 'edit-leave-requests', 'delete-leave-requests',
            'view-payslips', 'create-payslips', 'download-payslips',

            // Submissions
            'create-submissions', 'grade-submissions',

            // Reports
            'view-reports', 'generate-reports',

            // Settings
            'manage-settings', 'manage-roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- Roles ---

        // Super Admin: full access
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // School Admin: manage everything except system settings
        $schoolAdmin = Role::firstOrCreate(['name' => 'school-admin']);
        $schoolAdmin->givePermissionTo([
            'view-applications', 'edit-applications', 'delete-applications',
            'view-users', 'create-users', 'edit-users', 'delete-users',
            'view-departments', 'create-departments', 'edit-departments', 'delete-departments',
            'view-academic-years', 'create-academic-years', 'edit-academic-years', 'delete-academic-years',
            'view-cohorts', 'create-cohorts', 'edit-cohorts', 'delete-cohorts',
            'view-students', 'create-students', 'edit-students', 'delete-students',
            'view-student-grades', 'view-student-attendance',
            'view-staff', 'create-staff', 'edit-staff', 'delete-staff',
            'view-courses', 'create-courses', 'edit-courses', 'delete-courses',
            'view-announcements', 'create-announcements', 'edit-announcements', 'delete-announcements',
            'view-posts', 'create-posts', 'edit-posts', 'delete-posts',
            'view-leave-requests', 'create-leave-requests', 'edit-leave-requests', 'delete-leave-requests',
            'view-payslips', 'create-payslips', 'download-payslips',
            'view-reports', 'generate-reports',
        ]);

        // Department Head: manage own department's data
        $departmentHead = Role::firstOrCreate(['name' => 'department-head']);
        $departmentHead->givePermissionTo([
            'view-applications',
            'view-users',
            'view-departments',
            'view-academic-years',
            'view-cohorts', 'create-cohorts', 'edit-cohorts',
            'view-students', 'create-students', 'edit-students',
            'view-student-grades', 'manage-student-grades',
            'view-student-attendance', 'manage-student-attendance',
            'view-staff',
            'view-courses', 'create-courses', 'edit-courses',
            'view-lessons', 'create-lessons', 'edit-lessons',
            'view-announcements', 'create-announcements', 'edit-announcements',
            'view-posts', 'create-posts', 'edit-posts',
            'view-leave-requests',
            'view-reports', 'generate-reports',
        ]);

        // Academic Staff: teach, grade, attend
        $academicStaff = Role::firstOrCreate(['name' => 'academic_staff']);
        $academicStaff->givePermissionTo([
            'view-departments',
            'view-cohorts',
            'view-students',
            'view-student-grades', 'manage-student-grades',
            'view-student-attendance', 'manage-student-attendance',
            'view-courses',
            'view-lessons', 'create-lessons', 'edit-lessons',
            'view-announcements', 'create-announcements',
            'view-posts', 'create-posts',
            'grade-submissions',
        ]);

        // Non-Academic Staff: Basic access
        $nonAcademicStaff = Role::firstOrCreate(['name' => 'non_academic_staff']);
        $nonAcademicStaff->givePermissionTo([
            'view-announcements',
            'view-posts',
            'create-leave-requests',
            'view-payslips',
        ]);

        // Student: read-only on most things, can post in engagement
        $student = Role::firstOrCreate(['name' => 'student']);
        $student->givePermissionTo([
            'view-departments',
            'view-cohorts',
            'view-courses',
            'view-lessons',
            'view-announcements',
            'view-posts', 'create-posts',
            'create-submissions',
        ]);

        // --- Seed users ---
        $super = User::firstOrCreate(
            ['email' => 'super@hbci.ac.ls'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $super->assignRole('super-admin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@hbci.ac.ls'],
            [
                'name' => 'School Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('school-admin');

        $studentUser = User::firstOrCreate(
            ['email' => 'student@hbci.ac.ls'],
            [
                'name' => 'Student User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $studentUser->assignRole('student');

        $academicStaffUser = User::firstOrCreate(
            ['email' => 'academic@hbci.ac.ls'],
            [
                'name' => 'Academic Staff',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $academicStaffUser->assignRole('academic_staff');

        $nonAcademicStaffUser = User::firstOrCreate(
            ['email' => 'nonacademic@hbci.ac.ls'],
            [
                'name' => 'Non-Academic Staff',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $nonAcademicStaffUser->assignRole('non_academic_staff');

        $departmentHeadUser = User::firstOrCreate(
            ['email' => 'head@hbci.ac.ls'],
            [
                'name' => 'Department Head',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $departmentHeadUser->assignRole('department-head');
    }
}