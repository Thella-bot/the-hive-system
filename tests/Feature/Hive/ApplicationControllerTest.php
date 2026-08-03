<?php

namespace Tests\Feature\Hive;

use App\Models\Programme;
use App\Models\User;

class ApplicationControllerTest extends HiveTestCase
{
    public function test_application_index_shows_pending_applications_for_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        \App\Models\Application::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.applications.index'));

        $response->assertOk();
    }

    public function test_application_index_shows_all_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        \App\Models\Application::factory()->count(5)->create();

        $this->actingAs($admin);

        $response = $this->get(route('hive.applications.index'));

        $response->assertOk();
    }

    public function test_application_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        Programme::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.applications.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Applications/Create'));
    }

    public function test_application_store_creates_new_application(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        Programme::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.applications.store'), [
            'name' => 'Test Applicant',
            'email' => 'applicant@example.com',
            'programme_id' => Programme::first()->id,
        ]);

        $response->assertRedirect(route('hive.applications.index'));
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'programme_id' => Programme::first()->id,
        ]);
    }

    public function test_application_store_validates_programme_id(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->post(route('hive.applications.store'), [
            'programme_id' => '',
        ]);

        $response->assertSessionHasErrors(['programme_id']);
    }

    public function test_application_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        Programme::factory()->create();
        $application = \App\Models\Application::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.applications.show', $application));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Applications/Show'));
    }

    public function test_application_update_approves_application_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Programme::factory()->create();
        $application = \App\Models\Application::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->patch(route('hive.applications.update', $application), [
            'status' => 'approved',
            'notes' => 'Application accepted',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'approved',
        ]);
    }

    public function test_application_update_rejects_application(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Programme::factory()->create();
        $application = \App\Models\Application::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->patch(route('hive.applications.update', $application), [
            'status' => 'rejected',
            'notes' => 'Insufficient qualifications',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'rejected',
        ]);
    }

    public function test_application_destroy_deletes_application(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $application = \App\Models\Application::factory()->create();

        $this->actingAs($admin);

        $response = $this->delete(route('hive.applications.destroy', $application));

        $response->assertRedirect(route('hive.applications.index'));
        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
    }
}