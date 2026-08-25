<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2000-01-15',
            'phone' => '+1234567890',
            'address' => '123 Main Street',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '+0987654321',
            'annual_leave_days' => 20,
            'leave_balance' => 15.0,
            'profile_picture_path' => null,
            'twitter_handle' => null,
            'linkedin_profile' => null,
            'employee_number' => null,
            'department_id' => null,
            'designation' => null,
            'specialization' => null,
            'bio' => null,
            'hire_date' => null,
            'student_number' => null,
            'cohort_id' => null,
            'enrollment_date' => null,
            'expected_graduation_date' => null,
            'graduation_date' => null,
            'status' => 'active',
            'dietary_restrictions' => [],
            'emergency_contact_relationship' => 'Parent',
        ];
    }

    public function forStudent(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_number' => 'STU-' . random_int(1000, 9999),
            'status' => 'active',
        ]);
    }

    public function forStaff(): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_number' => 'EMP-' . random_int(1000, 9999),
            'designation' => 'Instructor',
        ]);
    }

    public function withDepartment(int $departmentId): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => $departmentId,
        ]);
    }

    public function withCohort(int $cohortId): static
    {
        return $this->state(fn (array $attributes) => [
            'cohort_id' => $cohortId,
        ]);
    }
}