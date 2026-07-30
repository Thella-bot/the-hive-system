<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_enum_has_all_expected_roles(): void
    {
        $expectedRoles = [
            'super-admin',
            'it-support',
            'academic-director',
            'program-coordinator',
            'chef-instructor',
            'pastry-instructor',
            'sous-chef',
            'student',
            'admissions-officer',
            'registrar',
            'examination-cell',
            'finance',
            'procurement-manager',
            'storekeeper',
            'hr-manager',
            'librarian',
            'career-services',
            'events-pr-manager',
            'cafeteria-manager',
            'parent-guardian',
            'alumni',
        ];

        foreach ($expectedRoles as $role) {
            $this->assertTrue(UserRole::tryFrom($role)->value === $role);
        }
    }

    public function test_display_name_returns_valid_string(): void
    {
        $this->assertIsString(UserRole::SUPER_ADMIN->displayName());
        $this->assertEquals('Super Admin', UserRole::SUPER_ADMIN->displayName());
        $this->assertEquals('Student', UserRole::STUDENT->displayName());
    }

    public function test_domain_returns_valid_string(): void
    {
        $this->assertIsString(UserRole::SUPER_ADMIN->domain());
        $this->assertEquals('System Administration', UserRole::SUPER_ADMIN->domain());
        $this->assertEquals('Student Services', UserRole::STUDENT->domain());
    }

    public function test_is_staff_returns_false_for_student_parent_alumni(): void
    {
        $this->assertFalse(UserRole::STUDENT->isStaff());
        $this->assertFalse(UserRole::PARENT_GUARDIAN->isStaff());
        $this->assertFalse(UserRole::ALUMNI->isStaff());
    }

    public function test_is_staff_returns_true_for_employee_roles(): void
    {
        $this->assertTrue(UserRole::SUPER_ADMIN->isStaff());
        $this->assertTrue(UserRole::CHEF_INSTRUCTOR->isStaff());
        $this->assertTrue(UserRole::FINANCE->isStaff());
    }

    public function test_is_faculty_returns_true_for_instructor_roles(): void
    {
        $this->assertTrue(UserRole::CHEF_INSTRUCTOR->isFaculty());
        $this->assertTrue(UserRole::PASTRY_INSTRUCTOR->isFaculty());
        $this->assertTrue(UserRole::SOUS_CHEF->isFaculty());
        $this->assertTrue(UserRole::ACADEMIC_DIRECTOR->isFaculty());
    }

    public function test_is_faculty_returns_false_for_non_instructor_roles(): void
    {
        $this->assertFalse(UserRole::STUDENT->isFaculty());
        $this->assertFalse(UserRole::SUPER_ADMIN->isFaculty());
        $this->assertFalse(UserRole::ADMISSIONS_OFFICER->isFaculty());
    }
}