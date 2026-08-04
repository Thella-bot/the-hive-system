<?php

namespace Tests\Feature\Hive;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrarControllerTest extends HiveTestCase
{
    public function test_registrar_registrations_index_requires_registrar_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.registrar.registrations.index'));

        $response->assertRedirect();
    }

    public function test_registrar_registrations_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        $response = $this->get(route('hive.registrar.registrations.index'));

        $response->assertOk();
    }

    public function test_registrar_enrollments_index_requires_registrar_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.registrar.enrollments.index'));

        $response->assertRedirect();
    }

    public function test_registrar_enrollments_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        $response = $this->get(route('hive.registrar.enrollments.index'));

        $response->assertOk();
    }

    public function test_registrar_enrollments_store_creates_enrollment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        $student = User::factory()->create()->assignRole('student');
        $programme = \App\Models\Programme::factory()->create();

        $response = $this->post(route('hive.registrar.enrollments.store'), [
            'user_id' => $student->id,
            'programme_id' => $programme->id,
        ]);

        $response->assertRedirect();
    }
}