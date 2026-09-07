<?php

namespace Tests\Feature\Hive;

use App\Models\Programme;
use App\Models\ProgrammeSought;
use App\Models\User;

class ProgrammeSoughtControllerTest extends HiveTestCase
{
    public function test_index_returns_success_for_academic_director(): void
    {
        $user = User::factory()->create();
        $user->assignRole('academic-director');
        ProgrammeSought::factory()->count(2)->create(['status' => 'pending']);

        $this->actingAs($user);

        $response = $this->get('/hive/academic/programme-seeks');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Applications/Index'));
    }

    public function test_index_returns_403_for_student(): void
    {
        $this->actingAsStudent();

        $response = $this->get('/hive/academic/programme-seeks');

        $response->assertRedirect();
    }

    public function test_show_returns_success_for_program_coordinator(): void
    {
        $user = User::factory()->create();
        $user->assignRole('program-coordinator');
        $programme = Programme::factory()->create();
        $application = ProgrammeSought::factory()->create([
            'programme_id' => $programme->id,
        ]);

        $this->actingAs($user);

        $response = $this->get("/hive/academic/programme-seeks/{$application->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Applications/Show'));
    }

    public function test_show_returns_403_for_student(): void
    {
        $application = ProgrammeSought::factory()->create();

        $this->actingAsStudent();

        $response = $this->get("/hive/academic/programme-seeks/{$application->id}");

        $response->assertRedirect();
    }

    public function test_update_approves_application_and_creates_student(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $programme = Programme::factory()->create();
        $application = ProgrammeSought::factory()->create([
            'programme_id' => $programme->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->patch("/hive/academic/programme-seeks/{$application->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('programme_soughts', [
            'id' => $application->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => $application->email,
        ]);
    }

    public function test_update_returns_403_for_student(): void
    {
        $application = ProgrammeSought::factory()->create();

        $this->actingAsStudent();

        $response = $this->patch("/hive/academic/programme-seeks/{$application->id}");

        $response->assertRedirect();
    }

    public function test_destroy_marks_application_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $application = ProgrammeSought::factory()->create(['status' => 'pending']);

        $this->actingAs($admin);

        $response = $this->delete("/hive/academic/programme-seeks/{$application->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('programme_soughts', [
            'id' => $application->id,
            'status' => 'rejected',
        ]);
    }

    public function test_destroy_returns_403_for_student(): void
    {
        $application = ProgrammeSought::factory()->create();

        $this->actingAsStudent();

        $response = $this->delete("/hive/academic/programme-seeks/{$application->id}");

        $response->assertRedirect();
    }
}