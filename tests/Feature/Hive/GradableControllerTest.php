<?php

namespace Tests\Feature\Hive;

use App\Models\Gradable;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GradableControllerTest extends HiveTestCase
{
    public function test_gradable_index_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent-guardian');

        $this->actingAs($user);

        $response = $this->get(route('hive.gradables.index'));

        $response->assertForbidden();
    }

    public function test_gradable_index_returns_success_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.gradables.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Gradables/Index'));
    }

    public function test_gradable_module_select_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $response = $this->get(route('hive.gradables.module-select', ['type' => 'quiz']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Gradables/ModuleSelect'));
    }

    public function test_gradable_create_requires_staff_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.gradables.create'));

        $response->assertForbidden();
    }

    public function test_gradable_create_returns_success_for_instructor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        Module::factory()->create();

        $response = $this->get(route('hive.gradables.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Gradables/Create'));
    }

    public function test_gradable_store_creates_gradable(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $module = Module::factory()->create();

        $response = $this->post(route('hive.gradables.store'), [
            'type' => 'quiz',
            'module_id' => $module->id,
            'title' => 'Midterm Quiz',
            'description' => 'Quiz on module topics',
            'due_date' => '2026-08-31',
            'max_marks' => 100,
            'weight' => 20,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gradables', [
            'title' => 'Midterm Quiz',
            'module_id' => $module->id,
        ]);
    }

    public function test_gradable_destroy_deletes_gradable(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $gradable = Gradable::factory()->create();

        $response = $this->delete(route('hive.gradables.destroy', $gradable));

        $response->assertRedirect();
        $this->assertDatabaseMissing('gradables', ['id' => $gradable->id]);
    }

    public function test_submit_online_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent-guardian');

        $this->actingAs($user);

        $gradable = Gradable::factory()->create();

        $response = $this->post(route('hive.gradables.submit-online', $gradable));

        $response->assertForbidden();
    }
}