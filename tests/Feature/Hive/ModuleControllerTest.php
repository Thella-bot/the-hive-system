<?php

namespace Tests\Feature\Hive;

use App\Models\Department;
use App\Models\Programme;
use App\Models\User;
use Tests\Feature\Hive\Traits\CreatesAssessmentFixture;

class ModuleControllerTest extends HiveTestCase
{
    use CreatesAssessmentFixture;

    public function test_module_index_returns_success_for_student(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.modules.index'));

        $response->assertOk();
    }

    public function test_module_index_returns_success_for_academic_director(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $this->actingAs($user);

        $response = $this->get(route('hive.modules.index'));

        $response->assertOk();
    }

    public function test_module_create_returns_success(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $this->actingAs($user);

        Department::factory()->create();
        Programme::factory()->create();

        $response = $this->get(route('hive.modules.create'));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Modules/Create'));
    }

    public function test_module_store_creates_new_module(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $this->actingAs($user);

        Department::factory()->create();
        Programme::factory()->create();

        $response = $this->post(route('hive.modules.store'), [
            'name' => 'Test Module',
            'code' => 'TEST101',
            'description' => 'A test module',
            'credits' => 3,
            'delivery_mode' => 'online',
            'meeting_platform' => 'Zoom',
            'meeting_link' => 'https://example.com/class',
            'location' => 'Virtual Classroom',
            'department_id' => Department::first()->id,
            'programme_id' => Programme::first()->id,
        ]);

        $response->assertRedirect(route('hive.modules.index'));
        $this->assertDatabaseHas('modules', [
            'code' => 'TEST101',
            'delivery_mode' => 'online',
            'meeting_platform' => 'Zoom',
            'meeting_link' => 'https://example.com/class',
            'location' => 'Virtual Classroom',
        ]);
    }

    public function test_module_store_validates_required_fields(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $this->actingAs($user);

        $response = $this->post(route('hive.modules.store'), [
            'name' => '',
            'code' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'code', 'credits', 'department_id', 'programme_id']);
    }

    public function test_module_store_validates_unique_code(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $this->actingAs($user);

        Department::factory()->create();
        Programme::factory()->create();

        $module = \App\Models\Module::factory()->create();

        $response = $this->post(route('hive.modules.store'), [
            'name' => 'Another Module',
            'code' => $module->code,
            'description' => 'Duplicate code test',
            'credits' => 3,
            'department_id' => Department::first()->id,
            'programme_id' => Programme::first()->id,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_module_show_returns_success(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $module = \App\Models\Module::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.modules.show', $module));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Modules/Show'));
    }

    public function test_module_edit_returns_success(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $module = \App\Models\Module::factory()->create();

        Department::factory()->create();
        Programme::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.modules.edit', $module));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Modules/Edit'));
    }

    public function test_module_update_updates_module(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $module = \App\Models\Module::factory()->create();

        Department::factory()->create();
        Programme::factory()->create();

        $this->actingAs($user);

        $response = $this->patch(route('hive.modules.update', $module), [
            'name' => 'Updated Module',
            'code' => $module->code,
            'description' => 'Updated description',
            'credits' => 4,
            'delivery_mode' => 'in_person',
            'location' => 'Room 101',
            'department_id' => Department::first()->id,
            'programme_id' => Programme::first()->id,
        ]);

        $response->assertRedirect(route('hive.modules.index'));
        $this->assertDatabaseHas('modules', ['id' => $module->id, 'name' => 'Updated Module']);
    }

    public function test_module_destroy_deletes_module(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('super-admin');

        $module = \App\Models\Module::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.modules.destroy', $module));

        $response->assertRedirect(route('hive.modules.index'));
        $this->assertDatabaseMissing('modules', ['id' => $module->id]);
    }

    public function test_module_store_programme_creates_programme(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $this->actingAs($user);

        Department::factory()->create();

        $response = $this->post(route('hive.programmes.store'), [
            'name' => 'Test Programme',
            'code' => 'TEST-PROG',
            'description' => 'A test programme',
            'duration' => '4 years',
            'duration_months' => 48,
            'total_price' => 50000,
            'monthly_fee' => 1250,
            'registration_fee' => 500,
            'academic_resource_fee' => 300,
            'uniform_fee' => 200,
            'tools_cost' => 100,
            'requirements' => 'Matric certificate',
            'payment_method' => 'monthly',
            'intake_period' => 'January',
            'career_opportunities' => 'Chef career',
            'department_id' => Department::first()->id,
        ]);

        $response->assertRedirect(route('hive.programmes.index'));
        $this->assertDatabaseHas('programmes', ['name' => 'Test Programme']);
    }

    public function test_student_sees_enrolled_module_in_index(): void
    {
        $fixture = $this->createAssessmentFixture();

        $this->actingAs($fixture['student1']);

        $response = $this->get(route('hive.modules.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('modules.data', 1)
            ->where('modules.data.0.id', $fixture['module']->id)
        );
    }

    public function test_student_cannot_view_module_show_due_to_role_middleware(): void
    {
        $fixture = $this->createAssessmentFixture();

        $this->actingAs($fixture['student1']);

        $response = $this->get(route('hive.modules.show', $fixture['module']));

        $response->assertRedirect();
    }

    public function test_module_duplicate_creates_copy(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $module = \App\Models\Module::factory()->create(['code' => 'ORIG-101']);

        $this->actingAs($user);

        $response = $this->post(route('hive.modules.duplicate', $module));

        $response->assertRedirect();
        $this->assertDatabaseHas('modules', [
            'name' => $module->name . ' (Copy)',
        ]);
        // Should have 2 modules now
        $this->assertDatabaseCount('modules', 2);
    }

    public function test_module_duplicate_requires_authorization(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('student');

        $module = \App\Models\Module::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.modules.duplicate', $module));

        // Students are redirected due to role middleware
        $response->assertRedirect();
    }

    public function test_module_search_filters_results(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $department = Department::factory()->create();
        $programme = Programme::factory()->create();

        $module = \App\Models\Module::factory()->create([
            'name' => 'Pastry Basics',
            'code' => 'PB-101',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.modules.index', ['search' => 'Pastry']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('modules.data', 1)
            ->where('modules.data.0.id', $module->id)
        );
    }

    public function test_module_filter_by_department(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('academic-director');

        $department = Department::factory()->create();
        $otherDepartment = Department::factory()->create();
        $programme = Programme::factory()->create();

        $moduleA = \App\Models\Module::factory()->create([
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'name' => 'Module A',
        ]);
        \App\Models\Module::factory()->create([
            'department_id' => $otherDepartment->id,
            'programme_id' => $programme->id,
            'name' => 'Module B',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.modules.index', ['department_id' => $department->id]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('modules.data', 1)
            ->where('modules.data.0.id', $moduleA->id)
        );
    }
}
