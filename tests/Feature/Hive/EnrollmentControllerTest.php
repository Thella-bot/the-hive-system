<?php

namespace Tests\Feature\Hive;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnrollmentControllerTest extends HiveTestCase
{
    public function test_enrollment_index_requires_student_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.enrollment.index'));

        $response->assertRedirect();
    }

    public function test_enrollment_index_returns_success_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.enrollment.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Enrollment/Index'));
    }

    public function test_enrollment_store_creates_enrollment_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $module = Module::factory()->create();

        $response = $this->post(route('hive.enrollment.store'), [
            'module_id' => $module->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_enrollment_destroy_removes_enrollment_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();
        Enrollment::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('hive.enrollment.destroy', $module));

        $response->assertRedirect();
        $this->assertSoftDeleted('enrollments', [
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_student_sees_only_current_semester_modules(): void
    {
        $this->withoutMiddleware();

        $department = Department::factory()->create();
        $currentYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $cohort = Cohort::create([
            'name' => 'January 2026',
            'slug' => 'january-2026',
            'department_id' => $department->id,
            'academic_year_id' => $currentYear->id,
            'max_students' => 20,
            'is_active' => true,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
        ]);
        $programme = Programme::factory()->create(['department_id' => $department->id]);

        $semester1Module = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);
        $semester2Module = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);
        $otherYearModule = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);

        $programme->modules()->attach($semester1Module->id, ['year_level' => 1, 'semester' => '1', 'order_column' => 1]);
        $programme->modules()->attach($semester2Module->id, ['year_level' => 1, 'semester' => '2', 'order_column' => 2]);
        $programme->modules()->attach($otherYearModule->id, ['year_level' => 2, 'semester' => '2', 'order_column' => 3]);

        $user = User::factory()->create(['programme_id' => $programme->id]);
        $user->assignRole('student');
        $user->profile()->create([
            'cohort_id' => $cohort->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.enrollment.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('modules')
            ->where('modules', fn ($modules) => collect($modules)->contains('id', $semester2Module->id))
            ->where('modules', fn ($modules) => ! collect($modules)->contains('id', $semester1Module->id))
            ->where('modules', fn ($modules) => ! collect($modules)->contains('id', $otherYearModule->id))
        );
    }

    public function test_student_cannot_enroll_in_out_of_semester_module(): void
    {
        $this->withoutMiddleware();

        $department = Department::factory()->create();
        $currentYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $cohort = Cohort::create([
            'name' => 'January 2026',
            'slug' => 'january-2026',
            'department_id' => $department->id,
            'academic_year_id' => $currentYear->id,
            'max_students' => 20,
            'is_active' => true,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
        ]);
        $programme = Programme::factory()->create(['department_id' => $department->id]);

        $semester1Module = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);
        $semester2Module = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);

        $programme->modules()->attach($semester1Module->id, ['year_level' => 1, 'semester' => '1', 'order_column' => 1]);
        $programme->modules()->attach($semester2Module->id, ['year_level' => 1, 'semester' => '2', 'order_column' => 2]);

        $user = User::factory()->create(['programme_id' => $programme->id]);
        $user->assignRole('student');
        $user->profile()->create([
            'cohort_id' => $cohort->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.enrollment.store'), [
            'module_id' => $semester1Module->id,
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'module_id' => $semester1Module->id,
        ]);
    }

    public function test_student_can_enroll_in_current_semester_module(): void
    {
        $this->withoutMiddleware();

        $department = Department::factory()->create();
        $currentYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $cohort = Cohort::create([
            'name' => 'January 2026',
            'slug' => 'january-2026',
            'department_id' => $department->id,
            'academic_year_id' => $currentYear->id,
            'max_students' => 20,
            'is_active' => true,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
        ]);
        $programme = Programme::factory()->create(['department_id' => $department->id]);

        $semester2Module = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);

        $programme->modules()->attach($semester2Module->id, ['year_level' => 1, 'semester' => '2', 'order_column' => 1]);

        $user = User::factory()->create(['programme_id' => $programme->id]);
        $user->assignRole('student');
        $user->profile()->create([
            'cohort_id' => $cohort->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.enrollment.store'), [
            'module_id' => $semester2Module->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'module_id' => $semester2Module->id,
        ]);
    }

    public function test_repeating_student_sees_current_and_previous_year_modules(): void
    {
        $this->withoutMiddleware();

        $department = Department::factory()->create();
        $previousYear = AcademicYear::create([
            'name' => '2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_current' => false,
        ]);
        $currentYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $cohort = Cohort::create([
            'name' => 'January 2025',
            'slug' => 'january-2025',
            'department_id' => $department->id,
            'academic_year_id' => $previousYear->id,
            'max_students' => 20,
            'is_active' => true,
            'start_date' => '2025-01-01',
            'end_date' => '2025-03-31',
        ]);
        $programme = Programme::factory()->create(['department_id' => $department->id]);

        $year2Module = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);
        $year1Module = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);

        $programme->modules()->attach($year2Module->id, ['year_level' => 2, 'semester' => '2', 'order_column' => 1]);
        $programme->modules()->attach($year1Module->id, ['year_level' => 1, 'semester' => '2', 'order_column' => 2]);

        $user = User::factory()->create(['programme_id' => $programme->id]);
        $user->assignRole('student');
        $user->profile()->create([
            'cohort_id' => $cohort->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.enrollment.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('modules')
            ->where('modules', fn ($modules) => collect($modules)->contains('id', $year2Module->id))
            ->where('modules', fn ($modules) => collect($modules)->contains('id', $year1Module->id))
            ->where('isRepeatingYear', true)
        );
    }

    public function test_repeating_student_can_enroll_in_previous_year_module(): void
    {
        $this->withoutMiddleware();

        $department = Department::factory()->create();
        $previousYear = AcademicYear::create([
            'name' => '2025',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_current' => false,
        ]);
        $currentYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $cohort = Cohort::create([
            'name' => 'January 2025',
            'slug' => 'january-2025',
            'department_id' => $department->id,
            'academic_year_id' => $previousYear->id,
            'max_students' => 20,
            'is_active' => true,
            'start_date' => '2025-01-01',
            'end_date' => '2025-03-31',
        ]);
        $programme = Programme::factory()->create(['department_id' => $department->id]);

        $year1Module = Module::factory()->create(['programme_id' => $programme->id, 'department_id' => $department->id]);

        $programme->modules()->attach($year1Module->id, ['year_level' => 1, 'semester' => '2', 'order_column' => 1]);

        $user = User::factory()->create(['programme_id' => $programme->id]);
        $user->assignRole('student');
        $user->profile()->create([
            'cohort_id' => $cohort->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.enrollment.store'), [
            'module_id' => $year1Module->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'module_id' => $year1Module->id,
        ]);
    }

    public function test_enrolling_twice_is_idempotent(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $first = $this->post(route('hive.enrollment.store'), [
            'module_id' => $module->id,
        ]);

        $second = $this->post(route('hive.enrollment.store'), [
            'module_id' => $module->id,
        ]);

        $first->assertRedirect();
        $second->assertRedirect();

        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_dropping_unenrolled_module_succeeds_silently(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.enrollment.destroy', $module));

        $response->assertRedirect();
        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_module_capacity_is_not_enforced(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.enrollment.store'), [
            'module_id' => $module->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);
    }
}
