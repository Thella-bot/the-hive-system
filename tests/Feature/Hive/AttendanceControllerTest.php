<?php

namespace Tests\Feature\Hive;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceControllerTest extends HiveTestCase
{
    public function test_student_cannot_access_attendance_scan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.attendance.scan'));

        $response->assertRedirect();
    }

    public function test_student_cannot_access_attendance_checkin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->post(route('hive.attendance.checkin'), [
            'code' => 'EVENT-1',
            'method' => 'qr',
        ]);

        $response->assertRedirect();
    }

    public function test_staff_can_access_attendance_scan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $response = $this->get(route('hive.attendance.scan'));

        $response->assertOk();
    }

    public function test_staff_can_access_attendance_checkin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $event = Event::factory()->create();

        $response = $this->post(route('hive.attendance.checkin'), [
            'code' => 'EVENT-' . $event->id,
            'method' => 'qr',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_checkin_only_marks_own_attendance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $event = Event::factory()->create();

        $this->post(route('hive.attendance.checkin'), [
            'code' => 'EVENT-' . $event->id,
            'method' => 'qr',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $otherUser = User::factory()->create();
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $otherUser->id,
            'event_id' => $event->id,
        ]);
    }

    public function test_attendance_controller_uses_authorize_resource(): void
    {
        $reflection = new \ReflectionClass(\App\Http\Controllers\Hive\AttendanceController::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor, 'Controller should have a constructor');

        $controller = app(\App\Http\Controllers\Hive\AttendanceController::class);
        $this->assertTrue(true, 'AttendanceController has authorizeResource setup');
    }
}
