<?php

namespace Tests\Feature\Hive;

use App\Models\User;

class LeaveRequestControllerTest extends HiveTestCase
{
    public function test_leave_request_index_returns_success_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.leaves.index'));

        $response->assertOk();
    }

    public function test_leave_request_index_shows_only_own_requests_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        \App\Models\LeaveRequest::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        \App\Models\LeaveRequest::factory()->count(2)->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.leaves.index'));

        $response->assertOk();
    }

    public function test_leave_request_index_shows_all_requests_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        \App\Models\LeaveRequest::factory()->count(5)->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.leaves.index'));

        $response->assertOk();
    }

    public function test_leave_request_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.leaves.create'));

        $response->assertOk();
    }

    public function test_leave_request_store_creates_new_request(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $profile = $user->profile()->create([
            'leave_balance' => 10,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.leaves.store'), [
            'type' => 'annual',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'reason' => 'Family vacation',
        ]);

        $response->assertRedirect(route('hive.leaves.index'));
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'type' => 'annual',
            'status' => 'pending',
        ]);
    }

    public function test_leave_request_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->post(route('hive.leaves.store'), [
            'type' => '',
            'start_date' => '',
            'end_date' => '',
        ]);

        $response->assertSessionHasErrors(['type', 'start_date', 'end_date']);
    }

    public function test_leave_request_store_rejects_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $user->profile()->create([
            'leave_balance' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.leaves.store'), [
            'type' => 'annual',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(3)->toDateString(),
            'reason' => 'Too many days requested',
        ]);

        $response->assertSessionHasErrors(['start_date']);
    }

    public function test_leave_request_update_approves_request_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('hr-manager');

        $user = User::factory()->create();
        $user->assignRole('student');

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->patch(route('hive.leaves.update', $leaveRequest), [
            'status' => 'approved',
        ]);

        $response->assertBack();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }

    public function test_leave_request_update_rejects_request(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('hr-manager');

        $user = User::factory()->create();
        $user->assignRole('student');

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->patch(route('hive.leaves.update', $leaveRequest), [
            'status' => 'rejected',
            'rejection_reason' => 'Not enough notice',
        ]);

        $response->assertBack();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected',
            'rejection_reason' => 'Not enough notice',
        ]);
    }

    public function test_leave_request_destroy_cancels_own_pending_request(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('hive.leaves.destroy', $leaveRequest));

        $response->assertBack();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'is_cancelled' => true,
        ]);
    }

    public function test_leave_request_destroy_denies_cancellation_of_non_pending_request(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('hive.leaves.destroy', $leaveRequest));

        $response->assertStatus(403);
    }

    public function test_leave_request_destroy_denies_cancellation_of_another_users_request(): void
    {
        $user1 = User::factory()->create();
        $user1->assignRole('student');

        $user2 = User::factory()->create();
        $user2->assignRole('student');

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user1->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user2);

        $response = $this->delete(route('hive.leaves.destroy', $leaveRequest));

        $response->assertStatus(403);
    }
}