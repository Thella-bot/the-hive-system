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
}