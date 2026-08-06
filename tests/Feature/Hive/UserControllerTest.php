<?php

namespace Tests\Feature\Hive;

use App\Models\Department;
use App\Models\User;

class UserControllerTest extends HiveTestCase
{
    public function test_user_index_requires_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.users.index'));

        $response->assertRedirect();
    }

    public function test_user_index_returns_success_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->get(route('hive.users.index'));

        $response->assertOk();
    }

    public function test_user_index_paginates_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        User::factory()->count(5)->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.users.index'));

        $response->assertOk();
    }

    public function test_user_index_filters_by_search(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $this->actingAs($user);

        $response = $this->get(route('hive.users.index') . '?search=John');

        $response->assertOk();
    }

    public function test_user_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->get(route('hive.users.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Users/Create'));
    }

    public function test_user_store_creates_new_user_with_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        Department::factory()->create();

        $response = $this->post(route('hive.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'student',
            'department_id' => Department::first()->id,
        ]);

        $response->assertRedirect(route('hive.users.show', User::where('email', 'newuser@example.com')->first()));
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_user_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->post(route('hive.users.store'), [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'role']);
    }

    public function test_user_show_returns_success(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('student');

        $this->actingAs($admin);

        $response = $this->get(route('hive.users.show', $targetUser));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Users/Show'));
    }

    public function test_user_edit_returns_success(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('student');

        Department::factory()->create();

        $this->actingAs($admin);

        $response = $this->get(route('hive.users.edit', $targetUser));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Users/Edit'));
    }

    public function test_user_update_updates_user_and_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('student');

        $this->actingAs($admin);

        $response = $this->patch(route('hive.users.update', $targetUser), [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
            'role'  => 'registrar',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'name' => 'Updated Name']);
        $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'email' => 'updated@example.com']);
        $this->assertTrue($targetUser->fresh()->hasRole('registrar'));
    }

    public function test_user_destroy_deletes_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('student');

        $this->actingAs($admin);

        $response = $this->delete(route('hive.users.destroy', $targetUser));

        $response->assertRedirect(route('hive.users.index'));
        $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
    }

    public function test_user_destroy_prevents_self_deletion(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->delete(route('hive.users.destroy', $user));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}