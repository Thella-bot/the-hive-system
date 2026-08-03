<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'programme_id' => Programme::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'status' => 'pending',
            'notes' => $this->faker->sentence(),
        ];
    }
}
