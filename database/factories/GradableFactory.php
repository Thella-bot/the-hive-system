<?php

namespace Database\Factories;

use App\Models\Gradable;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradableFactory extends Factory
{
    protected $model = Gradable::class;

    public function definition(): array
    {
        return [
            'type' => 'assignment',
            'submission_type' => 'file_upload',
            'module_id' => Module::factory(),
            'instructor_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'due_date' => now()->addDays(14),
            'max_marks' => 100,
            'weight' => 20,
        ];
    }
}
