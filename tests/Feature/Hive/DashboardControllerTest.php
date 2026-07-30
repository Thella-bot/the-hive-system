<?php

namespace Tests\Feature\Hive;

use App\Models\User;

class DashboardControllerTest extends HiveTestCase
{
    public function test_dashboard_redirects_to_login_for_guests(): void
    {
        $response = $this->get(route('hive.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_returns_success_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->get(route('hive.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Dashboard'));
    }

    public function test_dashboard_returns_success_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Dashboard'));
    }

    public function test_dashboard_returns_success_for_chef_instructor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $response = $this->get(route('hive.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Dashboard'));
    }
}