<?php

namespace Tests\Unit\Models;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => RolePermissionSeeder::class]);
    }

    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_user_has_relationships(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->leaveRequests);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->payslips);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->submissions);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->applications);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->invoices);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->payments);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->expenses);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->bookLoans);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->bookReservations);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->placements);
    }

    public function test_is_staff_returns_true_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->assertTrue($user->isStaff());
        $this->assertTrue($user->isAdmin());
    }

    public function test_is_staff_returns_true_for_it_support(): void
    {
        $user = User::factory()->create();
        $user->assignRole('it-support');

        $this->assertTrue($user->isStaff());
        $this->assertTrue($user->isAdmin());
    }

    public function test_is_staff_returns_true_for_registrar(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->assertTrue($user->isStaff());
    }

    public function test_is_staff_returns_false_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->assertFalse($user->isStaff());
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_staff_returns_false_for_parent_guardian(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent-guardian');

        $this->assertFalse($user->isStaff());
    }

    public function test_is_staff_returns_false_for_alumni(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->assertFalse($user->isStaff());
    }

    public function test_is_student_returns_true_for_student_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->assertTrue($user->isStudent());
    }

    public function test_is_student_returns_false_for_non_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->assertFalse($user->isStudent());
    }

    public function test_is_parent_guardian_returns_true(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent-guardian');

        $this->assertTrue($user->isParentGuardian());
    }

    public function test_is_alumni_returns_true(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->assertTrue($user->isAlumni());
    }

    public function test_is_faculty_returns_true_for_chef_instructor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->assertTrue($user->isFaculty());
    }

    public function test_is_faculty_returns_true_for_academic_director(): void
    {
        $user = User::factory()->create();
        $user->assignRole('academic-director');

        $this->assertTrue($user->isFaculty());
    }

    public function test_is_faculty_returns_false_for_non_faculty(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->assertFalse($user->isFaculty());
    }

    public function test_can_access_finance_returns_true_for_finance_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->assertTrue($user->canAccessFinance());
    }

    public function test_can_access_finance_returns_true_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->assertTrue($user->canAccessFinance());
    }

    public function test_can_manage_students_returns_true_for_registrar(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->assertTrue($user->canManageStudents());
    }

    public function test_can_manage_students_returns_true_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->assertTrue($user->canManageStudents());
    }

    public function test_can_access_kitchen_returns_true_for_chef_instructor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->assertTrue($user->canAccessKitchen());
    }

    public function test_get_primary_role_returns_role_name(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->assertEquals('student', $user->getPrimaryRole());
    }

    public function test_get_primary_role_returns_null_for_user_without_roles(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->getPrimaryRole());
    }

    public function test_get_role_display_name_returns_display_name(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->assertEquals('Chef Instructor', $user->getRoleDisplayName());
    }

    public function test_get_role_display_name_returns_unknown_for_null_role(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('Unknown', $user->getRoleDisplayName());
    }

    public function test_get_role_names_returns_array_of_role_names(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['student', 'parent-guardian']);

        $this->assertIsArray($user->getRoleNames());
        $this->assertContains('student', $user->getRoleNames());
        $this->assertContains('parent-guardian', $user->getRoleNames());
    }

    public function test_type_attribute_returns_student_for_student_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->assertEquals('student', $user->type);
    }

    public function test_type_attribute_returns_parent_for_parent_guardian_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent-guardian');

        $this->assertEquals('parent', $user->type);
    }

    public function test_type_attribute_returns_alumni_for_alumni_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->assertEquals('alumni', $user->type);
    }

    public function test_type_attribute_returns_staff_for_staff_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->assertEquals('staff', $user->type);
    }

    public function test_search_scope_filters_by_name(): void
    {
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $results = User::search('John')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    public function test_search_scope_filters_by_email(): void
    {
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $results = User::search('john@example.com')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    public function test_staff_scope_excludes_students(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('registrar');
        $student = User::factory()->create();
        $student->assignRole('student');

        $results = User::staff()->get();

        $this->assertTrue($results->contains($staff));
        $this->assertFalse($results->contains($student));
    }

    public function test_students_scope_returns_only_students(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        User::factory()->create()->assignRole('registrar');

        $results = User::students()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->isStudent());
    }

    public function test_needs_registration_returns_false_for_no_application(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->needsRegistration());
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['student', 'parent-guardian']);

        $this->assertTrue($user->hasRole('student'));
        $this->assertTrue($user->hasRole('parent-guardian'));
    }

    public function test_user_has_hasroles_trait(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->assertTrue($user->hasRole('student'));
        $this->assertFalse($user->hasRole('nonexistent-role'));
    }
}