<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgrammeFactory extends Factory
{
    protected $model = Programme::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' Programme',
            'description' => $this->faker->sentence(10),
            'duration_years' => 1,
        ];
    }
}

