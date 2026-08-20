<?php

namespace Tests\Feature\Hive;

use App\Models\User;

class ProfileControllerTest extends HiveTestCase
{
    public function test_user_can_update_own_non_sensitive_profile_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');
        $profile = $user->profile()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'phone' => '1234567890',
            'bio' => 'Original bio',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '0987654321',
            'bio' => 'Updated bio',
        ]);

        $response->assertRedirect();
        $profile->refresh();
        $this->assertSame('Updated', $profile->first_name);
        $this->assertSame('0987654321', $profile->phone);
        $this->assertSame('Updated bio', $profile->bio);
    }

    public function test_user_cannot_update_sensitive_fields_on_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');
        $profile = $user->profile()->create([
            'first_name' => 'Student',
            'last_name' => 'Name',
            'student_number' => 'STU-000001',
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.profile.update'), [
            'first_name' => 'Updated',
            'student_number' => 'STU-HACKED',
            'status' => 'graduated',
        ]);

        $response->assertRedirect();
        $profile->refresh();
        $this->assertSame('Updated', $profile->first_name);
        $this->assertSame('STU-000001', $profile->student_number);
        $this->assertSame('active', $profile->status);
    }

    public function test_user_can_upload_profile_picture(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');
        $profile = $user->profile()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.profile.update'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'profile_picture' => \Illuminate\Http\UploadedFile::fake()->image('profile.jpg'),
        ]);

        $response->assertRedirect();
        $profile->refresh();
        $this->assertNotNull($profile->profile_picture_path);
        $this->assertStringContainsString('profile-pictures', $profile->profile_picture_path);
    }

    public function test_user_can_view_own_profile_edit_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');
        $user->profile()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.profile.edit'));

        $response->assertOk();
    }
}
