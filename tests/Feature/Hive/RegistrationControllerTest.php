<?php

namespace Tests\Feature\Hive;

use App\Models\Programme;
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

        Programme::factory()->create();
        \App\Models\Application::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'admitted_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.registration.store'), [
            'date_of_birth' => '2000-01-01',
            'phone' => '+266 1234 5678',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '+266 1234 5678',
            'emergency_contact_relationship' => 'Parent',
            'dietary_restrictions' => [],
            'payment_proof' => \Illuminate\Http\UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'registration_status' => 'submitted',
        ]);
    }

    public function test_registration_update_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        Programme::factory()->create();
        $application = \App\Models\Application::factory()->create([
            'admitted_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.registration.update', $application), [
            'registration_status' => 'completed',
        ]);

        if ($response->getStatusCode() !== 302) {
            dump($response->getContent());
        }

        $response->assertRedirect();
    }

    public function test_registration_proof_download_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        Programme::factory()->create();
        \App\Models\Application::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'admitted_at' => now(),
            'registration_status' => 'completed',
            'registered_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.registration.proof'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}