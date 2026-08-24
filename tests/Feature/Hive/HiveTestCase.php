<?php

namespace Tests\Feature\Hive;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class HiveTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', [
            '--class' => RolePermissionSeeder::class,
        ]);
    }

    protected function actingAsAdmin(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsFaculty(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsStudent(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsRegistrar(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsFinance(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsAcademicDirector(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('academic-director');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsProgramCoordinator(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('program-coordinator');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsLibrarian(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('librarian');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsAdmissionsOfficer(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('admissions-officer');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsExaminationCell(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('examination-cell');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsProcurementManager(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('procurement-manager');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsStorekeeper(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('storekeeper');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsHrManager(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('hr-manager');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsChefInstructor(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsItSupport(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('it-support');

        $this->actingAs($user);

        return $user;
    }

    protected function createRbacFixture(): array
    {
        $departmentA = \App\Models\Department::factory()->create([
            'name' => 'Culinary Arts',
        ]);

        $departmentB = \App\Models\Department::factory()->create([
            'name' => 'Pastry & Bakery',
        ]);

        $cohortA = \App\Models\Cohort::factory()->create([
            'department_id' => $departmentA->id,
            'name' => 'CA August 2026',
        ]);

        $cohortB = \App\Models\Cohort::factory()->create([
            'department_id' => $departmentB->id,
            'name' => 'PB August 2026',
        ]);

        $superAdmin = \App\Models\User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $itSupport = \App\Models\User::factory()->create();
        $itSupport->assignRole('it-support');

        $hrManager = \App\Models\User::factory()->create();
        $hrManager->assignRole('hr-manager');

        $academicDirector = \App\Models\User::factory()->create();
        $academicDirector->assignRole('academic-director');

        $student = \App\Models\User::factory()->create();
        $student->assignRole('student');

        return [
            'super_admin' => $superAdmin,
            'it_support' => $itSupport,
            'hr_manager' => $hrManager,
            'academic_director' => $academicDirector,
            'student' => $student,
            'department_a' => $departmentA,
            'department_b' => $departmentB,
            'cohort_a' => $cohortA,
            'cohort_b' => $cohortB,
        ];
    }
}