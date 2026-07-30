<?php

namespace Database\Factories;

use App\Models\StudentTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentTaskFactory extends Factory
{
    protected $model = StudentTask::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'due_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'completed' => false,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'completed' => false,
        ]);
    }
}