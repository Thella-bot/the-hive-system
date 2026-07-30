<?php

namespace Tests\Feature\Hive;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffControllerTest extends HiveTestCase
{
    public function test_staff_index_requires_staff_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.staff.index'));

        $response->assertForbidden();
    }

    public function test_staff_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $this->actingAs($user);

        $response = $this->get(route('hive.staff.index'));

        $response->assertOk();
    }

    public function test_staff_store_creates_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        $this->actingAs($user);

        $response = $this->post(route('hive.staff.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'staff',
        ]);

        $response->assertRedirect();
    }

    public function test_staff_destroy_deletes_user(): void
    {
        $user = User::factory()->create()->assignRole('staff');

        $admin = User::factory()->create();
        $admin->assignRole('hr-manager');

        $this->actingAs($admin);

        $response = $this->delete(route('hive.staff.destroy', $user));

        $response->assertRedirect();
    }
}