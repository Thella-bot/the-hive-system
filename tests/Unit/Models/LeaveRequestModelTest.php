<?php

namespace Tests\Unit\Models;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_request_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_leave_request_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $leaveRequest->user);
        $this->assertEquals($user->id, $leaveRequest->user->id);
    }

    public function test_leave_request_belongs_to_approved_by_user(): void
    {
        $approver = User::factory()->create();
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'approved_by' => $approver->id,
        ]);

        $this->assertInstanceOf(User::class, $leaveRequest->approvedBy);
        $this->assertEquals($approver->id, $leaveRequest->approvedBy->id);
    }

    public function test_days_calculates_full_day_request_correctly(): void
    {
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-05',
            'half_day' => false,
        ]);

        $this->assertEquals(5.0, $leaveRequest->days());
    }

    public function test_days_calculates_half_day_request_correctly(): void
    {
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-05',
            'half_day' => true,
        ]);

        $this->assertEquals(0.5, $leaveRequest->days());
    }

    public function test_days_calculates_single_day_request_correctly(): void
    {
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-01',
            'half_day' => false,
        ]);

        $this->assertEquals(1.0, $leaveRequest->days());
    }

    public function test_has_sufficient_balance_returns_true_for_non_annual_leave(): void
    {
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'type' => 'sick',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-05',
        ]);

        $this->assertTrue($leaveRequest->hasSufficientBalance());
    }

    public function test_has_sufficient_balance_returns_true_when_balance_is_enough(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $profile = $user->profile;
        if ($profile) {
            $profile->update(['leave_balance' => 10.0]);
        }

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-03',
        ]);

        $this->assertTrue($leaveRequest->hasSufficientBalance());
    }

    public function test_has_sufficient_balance_returns_false_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $profile = $user->profile;
        if ($profile) {
            $profile->update(['leave_balance' => 1.0]);
        }

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-05',
        ]);

        $this->assertFalse($leaveRequest->hasSufficientBalance());
    }

    public function test_deduct_from_balance_decrements_annual_leave_balance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $profile = $user->profile;
        if ($profile) {
            $profile->update(['leave_balance' => 10.0]);
        }

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-03',
        ]);

        $leaveRequest->deductFromBalance();

        $profile->refresh();
        $this->assertEquals(7.0, $profile->leave_balance);
    }

    public function test_deduct_from_balance_does_not_decrement_below_zero(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $profile = $user->profile;
        if ($profile) {
            $profile->update(['leave_balance' => 1.0]);
        }

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-05',
        ]);

        $leaveRequest->deductFromBalance();

        $profile->refresh();
        $this->assertEquals(0.0, $profile->leave_balance);
    }

    public function test_restore_balance_increments_annual_leave_balance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $profile = $user->profile;
        if ($profile) {
            $profile->update(['leave_balance' => 5.0]);
        }

        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-03',
        ]);

        $leaveRequest->restoreBalance();

        $profile->refresh();
        $this->assertEquals(8.0, $profile->leave_balance);
    }

    public function test_pending_scope_filters_pending_requests(): void
    {
        $pendingRequest = \App\Models\LeaveRequest::factory()->create([
            'status' => 'pending',
            'is_cancelled' => false,
        ]);
        \App\Models\LeaveRequest::factory()->create([
            'status' => 'approved',
            'is_cancelled' => false,
        ]);
        \App\Models\LeaveRequest::factory()->create([
            'status' => 'pending',
            'is_cancelled' => true,
        ]);

        $results = \App\Models\LeaveRequest::pending()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('pending', $results->first()->status);
    }

    public function test_cancelled_scope_filters_cancelled_requests(): void
    {
        $cancelledRequest = \App\Models\LeaveRequest::factory()->create([
            'is_cancelled' => true,
        ]);
        \App\Models\LeaveRequest::factory()->create(['is_cancelled' => false]);

        $results = \App\Models\LeaveRequest::cancelled()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is_cancelled);
    }

    public function test_for_user_scope_filters_by_user_id(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userRequest = \App\Models\LeaveRequest::factory()->create(['user_id' => $user->id]);
        \App\Models\LeaveRequest::factory()->create(['user_id' => $otherUser->id]);

        $results = \App\Models\LeaveRequest::forUser($user->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($user->id, $results->first()->user_id);
    }

    public function test_approved_scope_filters_approved_requests(): void
    {
        $approvedRequest = \App\Models\LeaveRequest::factory()->create(['status' => 'approved']);
        \App\Models\LeaveRequest::factory()->create(['status' => 'pending']);

        $results = \App\Models\LeaveRequest::approved()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('approved', $results->first()->status);
    }

    public function test_rejected_scope_filters_rejected_requests(): void
    {
        $rejectedRequest = \App\Models\LeaveRequest::factory()->create(['status' => 'rejected']);
        \App\Models\LeaveRequest::factory()->create(['status' => 'pending']);

        $results = \App\Models\LeaveRequest::rejected()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('rejected', $results->first()->status);
    }

    public function test_active_scope_filters_non_cancelled_requests(): void
    {
        $activeRequest = \App\Models\LeaveRequest::factory()->create(['is_cancelled' => false]);
        \App\Models\LeaveRequest::factory()->create(['is_cancelled' => true]);

        $results = \App\Models\LeaveRequest::active()->get();

        $this->assertCount(1, $results);
        $this->assertFalse($results->first()->is_cancelled);
    }

    public function test_fillable_attributes_are_settable(): void
    {
        $user = User::factory()->create();
        $leaveRequest = new \App\Models\LeaveRequest();
        $leaveRequest->user_id = $user->id;
        $leaveRequest->type = 'annual';
        $leaveRequest->half_day = false;
        $leaveRequest->start_date = '2026-01-01';
        $leaveRequest->end_date = '2026-01-05';
        $leaveRequest->reason = 'Vacation';
        $leaveRequest->status = 'pending';
        $leaveRequest->save();

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'type' => 'annual',
            'reason' => 'Vacation',
        ]);
    }

    public function test_casts_half_day_to_boolean(): void
    {
        $leaveRequest = \App\Models\LeaveRequest::factory()->create(['half_day' => true]);

        $this->assertIsBool($leaveRequest->half_day);
        $this->assertTrue($leaveRequest->half_day);
    }

    public function test_casts_dates_correctly(): void
    {
        $leaveRequest = \App\Models\LeaveRequest::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-05',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $leaveRequest->start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $leaveRequest->end_date);
    }
}