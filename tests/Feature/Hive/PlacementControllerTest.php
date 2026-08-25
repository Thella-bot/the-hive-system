<?php

namespace Tests\Feature\Hive;

use App\Models\Placement;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlacementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => \Database\Seeders\RolePermissionSeeder::class]);
    }

    public function test_placement_index_requires_authorized_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.placements.index'));

        $response->assertRedirect();
    }

    public function test_placement_index_returns_success_for_career_services(): void
    {
        $user = User::factory()->create();
        $user->assignRole('career-services');

        $this->actingAs($user);

        $response = $this->get(route('hive.placements.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Placements/Index'));
    }

    public function test_placement_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('career-services');

        $this->actingAs($user);

        $response = $this->get(route('hive.placements.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Placements/Create'));
    }

    public function test_placement_store_creates_placement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('career-services');

        $student = User::factory()->create();
        $programme = Programme::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.placements.store'), [
            'student_id' => $student->id,
            'programme_id' => $programme->id,
            'organisation_name' => 'Test Hotel',
            'organisation_address' => '123 Main St',
            'supervisor_name' => 'John Doe',
            'supervisor_contact' => '123-456-7890',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-01',
            'duration' => '3 months',
            'type' => 'Compulsory',
            'status' => 'pending',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('placements', [
            'student_id' => $student->id,
            'organisation_name' => 'Test Hotel',
        ]);
    }

    public function test_placement_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('career-services');

        $placement = Placement::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.placements.show', $placement));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Placements/Show'));
    }

    public function test_placement_update_modifies_placement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('career-services');

        $placement = Placement::factory()->create();

        $this->actingAs($user);

        $response = $this->patch(route('hive.placements.update', $placement), [
            'student_id' => $placement->student_id,
            'programme_id' => $placement->programme_id,
            'organisation_name' => 'Updated Hotel',
            'organisation_address' => $placement->organisation_address,
            'supervisor_name' => $placement->supervisor_name,
            'supervisor_contact' => $placement->supervisor_contact,
            'start_date' => $placement->start_date,
            'end_date' => $placement->end_date,
            'duration' => $placement->duration,
            'type' => $placement->type,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('placements', [
            'id' => $placement->id,
            'organisation_name' => 'Updated Hotel',
            'status' => 'active',
        ]);
    }

    public function test_placement_destroy_deletes_placement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $placement = Placement::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.placements.destroy', $placement));

        $response->assertRedirect();
        $this->assertSoftDeleted('placements', ['id' => $placement->id]);
    }

    public function test_non_authorized_role_cannot_delete_placement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('career-services');

        $placement = Placement::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.placements.destroy', $placement));

        $response->assertRedirect();
        $this->assertDatabaseHas('placements', ['id' => $placement->id]);
    }

    public function test_generate_letter_requires_authorized_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $placement = Placement::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.placements.letter-pdf', $placement));

        $response->assertRedirect();
    }

    public function test_generate_letter_returns_pdf_for_authorized_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('career-services');

        $placement = Placement::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.placements.letter-pdf', $placement));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
