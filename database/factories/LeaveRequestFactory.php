<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'annual',
            'half_day' => false,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'reason' => 'Vacation',
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'is_cancelled' => false,
            'cancelled_at' => null,
        ];
    }

    public function sick(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sick',
        ]);
    }

    public function halfDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'half_day' => true,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => 'Not enough leave balance',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cancelled' => true,
            'cancelled_at' => now(),
        ]);
    }
}