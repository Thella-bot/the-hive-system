<?php

namespace Tests\Feature\Hive;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserApprovalControllerTest extends HiveTestCase
{
    public function test_approve_users_index_requires_it_support_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.admin.approve-users'));

        $response->assertRedirect();
    }

    public function test_approve_users_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('it-support');

        $this->actingAs($user);

        $response = $this->get(route('hive.admin.approve-users'));

        $response->assertOk();
    }

    public function test_approve_users_approves_a_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('it-support');

        $pendingUser = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('hive.admin.approve-users.approve', $pendingUser));

        $response->assertRedirect();
    }

    public function test_import_users_page_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('it-support');

        $this->actingAs($user);

        $response = $this->get(route('hive.admin.import-users'));

        $response->assertOk();
    }
}