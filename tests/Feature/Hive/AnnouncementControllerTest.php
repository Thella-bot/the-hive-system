<?php

namespace Tests\Feature\Hive;

use App\Models\Module;
use App\Models\User;

class AnnouncementControllerTest extends HiveTestCase
{
    public function test_announcement_index_returns_success_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.announcements.index'));

        $response->assertOk();
    }

    public function test_announcement_index_shows_paginated_announcements(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        \App\Models\Announcement::factory()->count(3)->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.announcements.index'));

        $response->assertOk();
    }

    public function test_announcement_create_returns_success_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        Module::factory()->create();

        $response = $this->get(route('hive.announcements.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Announcements/Create'));
    }

    public function test_announcement_store_creates_new_announcement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        Module::factory()->create();

        $response = $this->post(route('hive.announcements.store'), [
            'title' => '重要通知',
            'body' => 'This is a test announcement body',
            'priority' => 'normal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', ['title' => '重要通知']);
    }

    public function test_announcement_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->post(route('hive.announcements.store'), [
            'title' => '',
            'body' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'body']);
    }

    public function test_announcement_edit_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $announcement = \App\Models\Announcement::factory()->create();

        $this->actingAs($user);

        Module::factory()->create();

        $response = $this->get(route('hive.announcements.edit', $announcement));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Announcements/Edit'));
    }

    public function test_announcement_update_updates_announcement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $announcement = \App\Models\Announcement::factory()->create();

        $this->actingAs($user);

        Module::factory()->create();

        $response = $this->patch(route('hive.announcements.update', $announcement), [
            'title' => 'Updated Title',
            'body' => 'Updated body content',
            'priority' => 'urgent',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'title' => 'Updated Title']);
    }

    public function test_announcement_destroy_deletes_announcement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $announcement = \App\Models\Announcement::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.announcements.destroy', $announcement));

        $response->assertRedirect();
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_announcement_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $announcement = \App\Models\Announcement::factory()->create(['priority' => 'normal']);

        $this->actingAs($user);

        $response = $this->get(route('hive.announcements.show', $announcement));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Announcements/Show'));
    }
}