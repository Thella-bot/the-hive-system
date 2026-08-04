<?php

namespace Tests\Feature\Hive;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnrollmentControllerTest extends HiveTestCase
{
    public function test_enrollment_index_requires_student_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.enrollment.index'));

        $response->assertRedirect();
    }

    public function test_enrollment_index_returns_success_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.enrollment.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Enrollment/Index'));
    }

    public function test_enrollment_store_creates_enrollment_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $module = \App\Models\Module::factory()->create();

        $response = $this->post(route('hive.enrollment.store'), [
            'module_id' => $module->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_enrollment_destroy_removes_enrollment_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = \App\Models\Module::factory()->create();
        \App\Models\Enrollment::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('hive.enrollment.destroy', $module));

        $response->assertRedirect();
        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);
    }
}