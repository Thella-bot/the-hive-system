<?php

namespace Tests\Feature\Hive;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffControllerTest extends HiveTestCase
{
    public function test_staff_index_requires_hr_manager_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.staff.index'));

        $response->assertRedirect();
    }

    public function test_staff_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $this->actingAs($user);

        $response = $this->get(route('hive.staff.index'));

        $response->assertOk();
    }

    public function test_staff_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $this->actingAs($user);

        $response = $this->get(route('hive.staff.create'));

        $response->assertOk();
    }

    public function test_staff_store_creates_new_staff(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $this->actingAs($user);

        $response = $this->post(route('hive.staff.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();
    }

    public function test_staff_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $staff = User::factory()->create();
        $staff->assignRole('chef-instructor');

        $this->actingAs($user);

        $response = $this->get(route('hive.staff.show', $staff));

        $response->assertOk();
    }

    public function test_staff_destroy_deletes_staff(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $staff = User::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.staff.destroy', $staff));

        $response->assertRedirect();
    }

    public function test_staff_destroy_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $staff = User::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.staff.destroy', $staff));

        $response->assertRedirect();
    }
}

