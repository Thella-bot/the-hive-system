<?php

namespace Tests\Feature\Hive;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

class AcademicYearControllerTest extends HiveTestCase
{
    private function seedDepartments(): void
    {
        Department::create(['name' => 'Hospitality Management', 'slug' => 'hospitality-management', 'is_active' => true]);
        Department::create(['name' => 'Patisseries', 'slug' => 'patisseries', 'is_active' => true]);
        Department::create(['name' => 'Administration', 'slug' => 'administration', 'is_active' => true]);
    }

    public function test_academic_year_index_requires_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.academic-years.index'));

        $response->assertRedirect();
    }

    public function test_academic_year_index_returns_success_for_super_admin(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('hive.academic-years.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/AcademicYears/Index'));
    }

    public function test_academic_year_create_returns_success(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('hive.academic-years.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/AcademicYears/Create'));
    }

    public function test_academic_year_store_creates_default_cohorts(): void
    {
        $this->seedDepartments();
        $this->actingAsAdmin();

        $response = $this->post(route('hive.academic-years.store'), [
            'name'       => '2027',
            'start_date' => '2027-01-01',
            'end_date'   => '2027-12-31',
            'is_current' => true,
        ]);

        $response->assertRedirect(route('hive.academic-years.index'));

        $nonAdminDepartments = Department::whereNotIn('name', ['Administration', 'Admin'])->count();
        $expectedCohorts = $nonAdminDepartments * 3;

        $this->assertDatabaseCount('cohorts', $expectedCohorts);
    }

    public function test_academic_year_store_excludes_admin_department(): void
    {
        $this->seedDepartments();
        $this->actingAsAdmin();

        $this->post(route('hive.academic-years.store'), [
            'name'       => '2027',
            'start_date' => '2027-01-01',
            'end_date'   => '2027-12-31',
        ]);

        $adminCohorts = Cohort::whereHas('department', fn ($q) => $q->whereIn('name', ['Administration', 'Admin']))->count();
        $this->assertEquals(0, $adminCohorts);
    }

    public function test_academic_year_store_creates_three_cohorts_per_department(): void
    {
        $this->seedDepartments();
        $this->actingAsAdmin();

        $year = AcademicYear::create([
            'name'       => '2027',
            'start_date' => '2027-01-01',
            'end_date'   => '2027-12-31',
        ]);

        $year->generateDefaultCohorts();

        $departments = Department::whereNotIn('name', ['Administration', 'Admin'])->get();

        foreach ($departments as $department) {
            $cohorts = Cohort::where('department_id', $department->id)
                ->orderBy('start_date')
                ->get();

            $this->assertCount(3, $cohorts, "Expected 3 cohorts for {$department->name}");

            $this->assertEquals('January 2027', $cohorts[0]->name);
            $this->assertEquals('2027-01-01', $cohorts[0]->start_date->format('Y-m-d'));
            $this->assertEquals('2027-03-31', $cohorts[0]->end_date->format('Y-m-d'));

            $this->assertEquals('April 2027', $cohorts[1]->name);
            $this->assertEquals('2027-04-01', $cohorts[1]->start_date->format('Y-m-d'));
            $this->assertEquals('2027-07-31', $cohorts[1]->end_date->format('Y-m-d'));

            $this->assertEquals('August 2027', $cohorts[2]->name);
            $this->assertEquals('2027-08-01', $cohorts[2]->start_date->format('Y-m-d'));
            $this->assertEquals('2027-10-31', $cohorts[2]->end_date->format('Y-m-d'));

            $this->assertFalse($cohorts[0]->is_active);
            $this->assertFalse($cohorts[1]->is_active);
            $this->assertFalse($cohorts[2]->is_active);
        }
    }

    public function test_academic_year_store_validates_required_fields(): void
    {
        $this->seedDepartments();
        $this->actingAsAdmin();

        $response = $this->post(route('hive.academic-years.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'start_date', 'end_date']);
    }

    public function test_generate_default_cohorts_is_idempotent(): void
    {
        $this->seedDepartments();

        $year = AcademicYear::create([
            'name'       => '2027',
            'start_date' => '2027-01-01',
            'end_date'   => '2027-12-31',
        ]);

        $count = $year->generateDefaultCohorts();
        $this->assertEquals(6, $count);

        $countAgain = $year->generateDefaultCohorts();
        $this->assertEquals(0, $countAgain);

        $this->assertDatabaseCount('cohorts', 6);
    }

    public function test_generate_default_cohorts_sets_is_active_based_on_current_date(): void
    {
        $this->seedDepartments();

        $year = AcademicYear::create([
            'name'       => '2026',
            'start_date' => '2026-01-01',
            'end_date'   => '2026-12-31',
        ]);

        $year->generateDefaultCohorts();

        $cohorts = Cohort::orderBy('start_date')->get();

        // January 2026 ended Mar 31 → inactive
        // April 2026 ended Jul 31 → inactive
        // August 2026 runs Aug–Nov → active
        $this->assertFalse($cohorts[0]->is_active);
        $this->assertFalse($cohorts[1]->is_active);
        $this->assertTrue($cohorts[2]->is_active);
    }

    public function test_update_cohort_status_command_deactivates_expired_cohorts(): void
    {
        $department = Department::create(['name' => 'Hospitality Management', 'slug' => 'hospitality-mgmt', 'is_active' => true]);

        Cohort::create([
            'name'           => 'January 2025',
            'slug'           => 'january-2025',
            'department_id'  => $department->id,
            'academic_year_id' => AcademicYear::create([
                'name'       => '2025',
                'start_date' => '2025-01-01',
                'end_date'   => '2025-12-31',
            ])->id,
            'max_students' => 20,
            'is_active'    => true,
            'start_date'   => '2025-01-01',
            'end_date'     => '2025-03-31',
        ]);

        Cohort::create([
            'name'           => 'August 2026',
            'slug'           => 'august-2026',
            'department_id'  => $department->id,
            'academic_year_id' => AcademicYear::create([
                'name'       => '2026',
                'start_date' => '2026-01-01',
                'end_date'   => '2026-12-31',
            ])->id,
            'max_students' => 20,
            'is_active'    => false,
            'start_date'   => '2026-08-01',
            'end_date'     => '2026-11-30',
        ]);

        Artisan::call('cohorts:update-status');

        $this->assertFalse(Cohort::where('name', 'January 2025')->first()->is_active);
        $this->assertTrue(Cohort::where('name', 'August 2026')->first()->is_active);
    }
}
