<?php

namespace Tests\Feature\Hive;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => \Database\Seeders\RolePermissionSeeder::class]);
    }

    public function test_announcement_index_returns_success_for_student(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        Announcement::factory()->create(['target_roles' => ['student']]);

        $this->actingAs($student);

        $response = $this->get(route('hive.announcements.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Announcements/Index'));
    }

    public function test_student_cannot_view_announcement_not_targeted_to_them(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $announcement = Announcement::factory()->create([
            'target_roles' => ['finance'],
        ]);

        $this->actingAs($student);

        $response = $this->get(route('hive.announcements.show', $announcement));

        $response->assertRedirect();
    }

    public function test_student_cannot_create_announcement(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student);

        $response = $this->get(route('hive.announcements.create'));

        $response->assertRedirect();
    }

    public function test_staff_can_create_announcement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('registrar');

        $this->actingAs($user);

        $response = $this->get(route('hive.announcements.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Announcements/Create'));
    }

    public function test_creator_can_update_own_announcement(): void
    {
        $creator = User::factory()->create();
        $creator->assignRole('registrar');

        $announcement = Announcement::factory()->create([
            'created_by' => $creator->id,
        ]);

        $this->actingAs($creator);

        $response = $this->get(route('hive.announcements.edit', $announcement));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Announcements/Edit'));
    }

    public function test_super_admin_can_update_any_announcement(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $creator = User::factory()->create();
        $announcement = Announcement::factory()->create([
            'created_by' => $creator->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('hive.announcements.edit', $announcement));

        $response->assertOk();
    }

    public function test_student_cannot_delete_announcement(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $announcement = Announcement::factory()->create();

        $this->actingAs($student);

        $response = $this->delete(route('hive.announcements.destroy', $announcement));

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', ['id' => $announcement->id]);
    }

    public function test_non_creator_staff_cannot_update_announcement(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('finance');

        $creator = User::factory()->create();
        $announcement = Announcement::factory()->create([
            'created_by' => $creator->id,
            'target_roles' => ['finance'],
        ]);

        $this->actingAs($staff);

        $response = $this->get(route('hive.announcements.edit', $announcement));

        $response->assertRedirect();
    }

    public function test_download_attachment_requires_staff(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $announcement = Announcement::factory()->create([
            'target_roles' => ['student'],
        ]);
        $attachment = $announcement->attachments()->create([
            'name' => 'test.pdf',
            'file_path' => 'announcements/test.pdf',
            'size' => 1024,
            'uploaded_by' => User::factory()->create()->id,
        ]);

        $this->actingAs($student);

        $response = $this->get(route('hive.announcements.attachments.download', [
            'announcement' => $announcement,
            'attachment' => $attachment,
        ]));

        $response->assertRedirect();
    }
}
