<?php

namespace Database\Factories;

use App\Models\Programme;
use App\Models\ProgrammeSought;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgrammeSoughtFactory extends Factory
{
    protected $model = ProgrammeSought::class;

    public function definition(): array
    {
        return [
            'programme_id' => Programme::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'status' => 'pending',
            'notes' => $this->faker->sentence(),
        ];
    }
}