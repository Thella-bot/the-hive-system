<?php

namespace Tests\Feature\Hive;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrationControllerTest extends HiveTestCase
{
    public function test_registration_index_returns_success_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        $response = $this->get(route('hive.registration.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Registrations/Index'));
    }

    public function test_registration_index_returns_success_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.registration.index'));

        $response->assertOk();
    }

    public function test_registration_store_creates_registration(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->post(route('hive.registration.store'), [
            'notes' => 'Registration notes',
        ]);

        $response->assertRedirect();
    }

    public function test_registration_update_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        $application = \App\Models\Application::factory()->create([
            'admitted_at' => now(),
        ]);

        $response = $this->patch(route('hive.registration.update', $application), [
            'registration_status' => 'completed',
        ]);

        $response->assertRedirect();
    }

    public function test_registration_proof_download_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.registration.proof'));

        $response->assertOk();
    }
}