<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'body' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['general', 'academic', 'event', 'hr', 'module', 'student', 'staff', 'administrative', 'financial', 'health_safety']),
            'is_pinned' => $this->faker->boolean(20),
            'created_by' => User::factory(),
        ];
    }
}
