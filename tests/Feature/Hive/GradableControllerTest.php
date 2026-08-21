<?php

namespace Tests\Feature\Hive;

use App\Models\Enrollment;
use App\Models\Gradable;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Hive\Traits\CreatesAssessmentFixture;

class GradableControllerTest extends HiveTestCase
{
    use CreatesAssessmentFixture;

    public function test_assessment_fixture_is_valid(): void
    {
        $fixture = $this->createAssessmentFixture();

        $this->assertTrue($fixture['instructor']->hasRole('chef-instructor'), 'Instructor must have chef-instructor role');
        $this->assertTrue($fixture['student1']->hasRole('student'), 'Student 1 must have student role');
        $this->assertTrue($fixture['student2']->hasRole('student'), 'Student 2 must have student role');

        $this->assertTrue(
            $fixture['module']->instructors()->where('users.id', $fixture['instructor']->id)->exists(),
            'Module must have instructor attached'
        );
        $this->assertTrue(
            Enrollment::where('user_id', $fixture['student1']->id)
                ->where('module_id', $fixture['module']->id)
                ->exists(),
            'Student 1 must be enrolled in module'
        );
        $this->assertFalse(
            Enrollment::where('user_id', $fixture['student2']->id)->exists(),
            'Student 2 must not be enrolled in any module'
        );

        $this->assertSame('assignment', $fixture['gradable']->type->value);
        $this->assertSame($fixture['module']->id, $fixture['gradable']->module_id);
        $this->assertSame($fixture['instructor']->id, $fixture['gradable']->instructor_id);
        $this->assertTrue($fixture['gradable']->due_date->gt(now()));
    }

    public function test_gradable_index_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('parent-guardian');

        $this->actingAs($user);

        $response = $this->get(route('hive.gradables.index'));

        $response->assertRedirect();
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

        $response->assertRedirect();
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

        $response->assertRedirect();
    }

    public function test_student_not_enrolled_cannot_view_gradable(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();
        $gradable = Gradable::factory()->create(['module_id' => $module->id]);

        $this->actingAs($user);

        $response = $this->get(route('hive.gradables.show', $gradable));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_student_enrolled_can_view_gradable(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();
        $gradable = Gradable::factory()->create(['module_id' => $module->id]);

        $user->modules()->attach($module->id);

        $this->actingAs($user);

        $response = $this->get(route('hive.gradables.show', $gradable));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Gradables/Show'));
    }

    public function test_student_is_rejected_from_all_staff_gradable_actions(): void
    {
        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];

        $staffRoutes = [
            'hive.gradables.create' => 'GET',
            'hive.gradables.store' => 'POST',
            'hive.gradables.update' => 'PUT',
            'hive.gradables.destroy' => 'DELETE',
            'hive.gradables.questions.store' => 'POST',
            'hive.gradables.attachments.store' => 'POST',
        ];

        foreach ($staffRoutes as $routeName => $method) {
            $params = in_array($method, ['POST', 'PUT']) ? [
                'type' => 'quiz',
                'module_id' => $fixture['module']->id,
                'title' => 'Test',
                'description' => 'Test',
                'due_date' => now()->addDay(),
                'max_marks' => 100,
                'weight' => 20,
            ] : [];

            $response = $this->call($method, route($routeName, $gradable), $params);
            $this->assertNotEquals(200, $response->status(), "Student should not get 200 from $routeName");
        }
    }

    public function test_policy_route_mismatch_it_support(): void
    {
        $this->markTestIncomplete('Policy/route mismatch: it-support is in GradablePolicy::create/update/delete but NOT in the route middleware group. See routes/hive/assessments.php:27 vs app/Policies/GradablePolicy.php:39-47, 50-59, 62-72.');
    }

    public function test_policy_route_mismatch_examination_cell(): void
    {
        $this->markTestIncomplete('Policy/route mismatch: examination-cell is in the route middleware group (assessments.php:27) but NOT in GradablePolicy::update/delete (policy lines 50-59, 62-72). It IS in GradablePolicy::create (line 46).');
    }
}
