<?php

namespace Tests\Feature\Hive;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CohortControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => \Database\Seeders\RolePermissionSeeder::class]);
    }

    public function test_cohort_index_requires_authorized_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.cohorts.index'));

        $response->assertRedirect();
    }

    public function test_cohort_index_returns_success_for_program_coordinator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('program-coordinator');

        $this->actingAs($user);

        $response = $this->get(route('hive.cohorts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Cohorts/Index'));
    }

    public function test_cohort_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('program-coordinator');

        $department = Department::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $cohort = Cohort::factory()->create([
            'department_id' => $department->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.cohorts.show', $cohort));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Cohorts/Show'));
    }

    public function test_cohort_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('program-coordinator');

        $this->actingAs($user);

        $response = $this->get(route('hive.cohorts.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Cohorts/Create'));
    }

    public function test_cohort_store_creates_cohort(): void
    {
        $user = User::factory()->create();
        $user->assignRole('program-coordinator');

        $department = Department::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.cohorts.store'), [
            'name' => 'Test Cohort',
            'department_id' => $department->id,
            'academic_year_id' => $academicYear->id,
            'max_students' => 20,
            'is_active' => true,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cohorts', ['name' => 'Test Cohort']);
    }

    public function test_cohort_update_updates_cohort(): void
    {
        $user = User::factory()->create();
        $user->assignRole('program-coordinator');

        $department = Department::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $cohort = Cohort::factory()->create([
            'department_id' => $department->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->actingAs($user);

        $response = $this->put(route('hive.cohorts.update', $cohort), [
            'name' => 'Updated Cohort',
            'department_id' => $department->id,
            'academic_year_id' => $academicYear->id,
            'max_students' => 25,
            'is_active' => true,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cohorts', ['id' => $cohort->id, 'name' => 'Updated Cohort']);
    }

    public function test_cohort_destroy_deletes_cohort(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $department = Department::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $cohort = Cohort::factory()->create([
            'department_id' => $department->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('hive.cohorts.destroy', $cohort));

        $response->assertRedirect(route('hive.cohorts.index'));
        $this->assertSoftDeleted('cohorts', ['id' => $cohort->id]);
    }

    public function test_cohort_destroy_blocks_deletion_when_students_enrolled(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $department = Department::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $cohort = Cohort::factory()->create([
            'department_id' => $department->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Create a student profile linked to this cohort
        $student = User::factory()->create();
        $student->assignRole('student');
        $student->profile()->create(['cohort_id' => $cohort->id]);

        $this->actingAs($user);

        $response = $this->delete(route('hive.cohorts.destroy', $cohort));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cohorts', ['id' => $cohort->id]);
    }
}
