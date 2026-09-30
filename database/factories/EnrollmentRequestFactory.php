<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EnrollmentRequest;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentRequestFactory extends Factory
{
    protected $model = EnrollmentRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'module_id' => Module::factory(),
            'type' => EnrollmentRequest::TYPE_ENROLLMENT,
            'academic_year' => (string) now()->year,
            'semester' => '1',
            'reason' => null,
            'status' => EnrollmentRequest::STATUS_PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => EnrollmentRequest::STATUS_PENDING]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => EnrollmentRequest::STATUS_APPROVED,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => EnrollmentRequest::STATUS_REJECTED,
            'reviewed_at' => now(),
        ]);
    }

    public function deregistration(): static
    {
        return $this->state(fn () => ['type' => EnrollmentRequest::TYPE_DEREGISTRATION]);
    }
}
