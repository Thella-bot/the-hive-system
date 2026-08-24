<?php

namespace Tests\Feature\Hive;

use App\Models\Cohort;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => \Database\Seeders\RolePermissionSeeder::class]);
    }

    public function test_department_index_requires_authorized_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.departments.index'));

        $response->assertRedirect();
    }

    public function test_department_index_returns_success_for_academic_director(): void
    {
        $user = User::factory()->create();
        $user->assignRole('academic-director');

        $this->actingAs($user);

        $response = $this->get(route('hive.departments.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Departments/Index'));
    }

    public function test_department_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('academic-director');

        $department = Department::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.departments.show', $department));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Departments/Show'));
    }

    public function test_department_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('academic-director');

        $this->actingAs($user);

        $response = $this->get(route('hive.departments.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Departments/Create'));
    }

    public function test_department_store_creates_department(): void
    {
        $user = User::factory()->create();
        $user->assignRole('academic-director');

        $this->actingAs($user);

        $response = $this->post(route('hive.departments.store'), [
            'name' => 'Test Department',
            'description' => 'A test department',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('hive.departments.index'));
        $this->assertDatabaseHas('departments', ['name' => 'Test Department']);
    }

    public function test_department_update_updates_department(): void
    {
        $user = User::factory()->create();
        $user->assignRole('academic-director');

        $department = Department::factory()->create();

        $this->actingAs($user);

        $response = $this->put(route('hive.departments.update', $department), [
            'name' => 'Updated Department',
            'description' => 'Updated description',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('hive.departments.show', $department));
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Updated Department']);
    }

    public function test_department_destroy_deletes_department(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $department = Department::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.departments.destroy', $department));

        $response->assertRedirect(route('hive.departments.index'));
        $this->assertSoftDeleted('departments', ['id' => $department->id]);
    }
}
