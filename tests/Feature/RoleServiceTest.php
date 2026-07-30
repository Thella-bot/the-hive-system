<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', [
            '--class' => \Database\Seeders\RolePermissionSeeder::class,
        ]);
    }

    public function test_is_admin_returns_true_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $roleService = new RoleService();

        $this->assertTrue($roleService->isAdmin($user));
    }

    public function test_is_admin_returns_true_for_it_support(): void
    {
        $user = User::factory()->create();
        $user->assignRole('it-support');

        $roleService = new RoleService();

        $this->assertTrue($roleService->isAdmin($user));
    }

    public function test_is_admin_returns_false_for_non_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $roleService = new RoleService();

        $this->assertFalse($roleService->isAdmin($user));
    }

    public function test_is_faculty_returns_true_for_chef_instructor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $roleService = new RoleService();

        $this->assertTrue($roleService->isFaculty($user));
    }

    public function test_is_faculty_returns_false_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $roleService = new RoleService();

        $this->assertFalse($roleService->isFaculty($user));
    }

    public function test_is_student_returns_true_for_student_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $roleService = new RoleService();

        $this->assertTrue($roleService->isStudent($user));
    }

    public function test_is_student_returns_false_for_non_student_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $roleService = new RoleService();

        $this->assertFalse($roleService->isStudent($user));
    }

    public function test_can_access_finance_returns_true_for_finance_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $roleService = new RoleService();

        $this->assertTrue($roleService->canAccessFinance($user));
    }

    public function test_can_access_finance_returns_false_for_student_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $roleService = new RoleService();

        $this->assertFalse($roleService->canAccessFinance($user));
    }

    public function test_can_manage_students_returns_true_for_registrar(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $roleService = new RoleService();

        $this->assertTrue($roleService->canManageStudents($user));
    }

    public function test_get_dashboard_data_type_returns_admin_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $roleService = new RoleService();

        $this->assertEquals('admin', $roleService->getDashboardDataType($user));
    }

    public function test_get_dashboard_data_type_returns_instructor_for_chef_instructor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $roleService = new RoleService();

        $this->assertEquals('instructor', $roleService->getDashboardDataType($user));
    }

    public function test_get_dashboard_data_type_returns_student_for_student_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $roleService = new RoleService();

        $this->assertEquals('student', $roleService->getDashboardDataType($user));
    }

    public function test_get_dashboard_data_type_returns_null_for_unknown_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent-guardian');

        $roleService = new RoleService();

        $this->assertNull($roleService->getDashboardDataType($user));
    }
}