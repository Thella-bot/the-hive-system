<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->address(),
            'start' => now()->addDays(7),
            'end' => now()->addDays(7)->addHours(2),
            'category' => 'event',
            'target_modules' => [],
        ];
    }
}
