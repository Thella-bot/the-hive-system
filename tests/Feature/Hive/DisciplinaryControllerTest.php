<?php

namespace Tests\Feature\Hive;

use App\Models\DisciplinaryAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplinaryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => \Database\Seeders\RolePermissionSeeder::class]);
    }

    public function test_disciplinary_index_requires_authorized_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.disciplinary.index'));

        $response->assertRedirect();
    }

    public function test_disciplinary_index_returns_success_for_hr_manager(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $this->actingAs($user);

        $response = $this->get(route('hive.disciplinary.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Disciplinary/Index'));
    }

    public function test_disciplinary_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $this->actingAs($user);

        $this->withoutExceptionHandling();
        $response = $this->get(route('hive.disciplinary.create'));

        $response->assertOk();
    }

    public function test_disciplinary_store_creates_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $student = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.disciplinary.store'), [
            'user_id' => $student->id,
            'type' => 'warning',
            'warning_level' => 'first',
            'offence' => 'Late submission',
            'incident_description' => 'Student submitted assignment late without excuse.',
            'hearing_date' => '2026-09-01',
            'effective_date' => '2026-09-01',
            'duration' => '1 week',
            'return_date' => '2026-09-08',
            'campus_access' => 'Full access',
            'surrender_date' => '2026-09-01',
            'review_date' => '2026-09-15',
            'grounds' => ['Late submission'],
            'policy_violated' => 'Academic Policy 3.1',
            'corrective_actions' => ['Attend time management workshop'],
            'advisor_name' => 'Dr. Smith',
            'hr_rep' => 'Jane Doe',
            'expiry_date' => '2026-12-01',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('disciplinary_actions', [
            'user_id' => $student->id,
            'type' => 'warning',
        ]);
    }

    public function test_disciplinary_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $action = DisciplinaryAction::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.disciplinary.show', $action));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Disciplinary/Show'));
    }

    public function test_disciplinary_update_modifies_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $action = DisciplinaryAction::factory()->create();

        $this->actingAs($user);

        $response = $this->patch(route('hive.disciplinary.update', $action), [
            'user_id' => $action->user_id,
            'type' => $action->type,
            'offence' => 'Updated offence',
            'incident_description' => 'Updated description.',
            'hearing_date' => $action->hearing_date,
            'effective_date' => $action->effective_date,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('disciplinary_actions', [
            'id' => $action->id,
            'offence' => 'Updated offence',
        ]);
    }

    public function test_disciplinary_destroy_deletes_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $action = DisciplinaryAction::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.disciplinary.destroy', $action));

        $response->assertRedirect();
        $this->assertSoftDeleted('disciplinary_actions', ['id' => $action->id]);
    }

    public function test_non_authorized_role_cannot_delete_disciplinary_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $action = DisciplinaryAction::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.disciplinary.destroy', $action));

        $response->assertRedirect();
        $this->assertDatabaseHas('disciplinary_actions', ['id' => $action->id]);
    }

    public function test_generate_warning_requires_authorized_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $action = DisciplinaryAction::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.disciplinary.warning-pdf', $action));

        $response->assertRedirect();
    }

    public function test_generate_warning_returns_pdf_for_authorized_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $action = DisciplinaryAction::factory()->create([
            'type' => 'warning',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.disciplinary.warning-pdf', $action));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
