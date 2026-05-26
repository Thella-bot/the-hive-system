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

        // --- Permissions ---
        $permissions = [
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
            'view-users', 'create-users', 'edit-users',
            'view-departments', 'create-departments', 'edit-departments',
            'view-academic-years', 'create-academic-years', 'edit-academic-years',
            'view-cohorts', 'create-cohorts', 'edit-cohorts',
            'view-students', 'create-students', 'edit-students',
            'view-student-grades', 'view-student-attendance',
            'view-staff', 'create-staff', 'edit-staff',
            'view-courses', 'create-courses', 'edit-courses',
            'view-announcements', 'create-announcements', 'edit-announcements', 'delete-announcements',
            'view-posts', 'create-posts', 'edit-posts', 'delete-posts',
            'view-reports', 'generate-reports',
        ]);

        // Department Head: manage own department's data
        $departmentHead = Role::firstOrCreate(['name' => 'department-head']);
        $departmentHead->givePermissionTo([
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
            'view-reports', 'generate-reports',
        ]);

        // Chef Instructor: teach, grade, attend
        $instructor = Role::firstOrCreate(['name' => 'chef-instructor']);
        $instructor->givePermissionTo([
            'view-departments',
            'view-cohorts',
            'view-students',
            'view-student-grades', 'manage-student-grades',
            'view-student-attendance', 'manage-student-attendance',
            'view-courses',
            'view-lessons', 'create-lessons', 'edit-lessons',
            'view-announcements', 'create-announcements',
            'view-posts', 'create-posts',
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
        ]);

        // --- Seed super admin user ---
        $admin = User::firstOrCreate(
            ['email' => 'root@hbci.ac.ls'],
            [
                'name' => 'HBCI Administrator',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super-admin');
    }
}
