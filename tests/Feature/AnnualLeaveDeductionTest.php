<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnualLeaveDeductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_annual_leave_days_are_counted_inclusive_and_deducted_on_approval(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $hrStaff = User::factory()->create();
        $hrStaff->assignRole('hr_staff');

        $staffUser = User::factory()->create();
        $staffUser->assignRole('instructor');

        // ensure profile exists with sufficient balance
        $profile = $staffUser->profile()->create([
            'leave_balance' => 10,
        ]);






        $leave = $staffUser->leaveRequests()->create([
            'type' => 'annual',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'reason' => 'test',
            'status' => 'pending',
        ]);

        $this->actingAs($hrStaff);

        $response = $this->put(route('intranet.leaves.update', ['leave' => $leave->id]), [
            'status' => 'approved',
        ]);

        $response->assertSessionHas('success');

        $profile->refresh();
        // start..end inclusive => 3 days (D, D+1, D+2)
        $this->assertSame(7, (int) $profile->leave_balance);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
        ]);
    }
}

