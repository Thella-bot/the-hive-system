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

    public function test_registrar_enrollments_update_updates_enrollment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        $enrollment = \App\Models\Enrollment::factory()->create();

        $response = $this->patch(route('hive.registrar.enrollments.update', $enrollment), [
            'academic_year' => '2027',
            'semester' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'academic_year' => '2027',
            'semester' => 2,
        ]);
    }

    public function test_registrar_enrollments_destroy_removes_enrollment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        $enrollment = \App\Models\Enrollment::factory()->create();

        $response = $this->delete(route('hive.registrar.enrollments.destroy', $enrollment));

        $response->assertRedirect();
        $this->assertSoftDeleted('enrollments', ['id' => $enrollment->id]);
    }
}