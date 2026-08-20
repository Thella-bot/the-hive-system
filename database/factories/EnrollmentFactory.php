<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'module_id' => Module::factory(),
            'academic_year' => now()->format('Y'),
            'semester' => now()->month <= 6 ? '1' : '2',
        ];
    }
}
