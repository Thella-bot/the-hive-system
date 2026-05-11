<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'programme_id' => Programme::factory(),
            'name' => $this->faker->word(),
            'code' => strtoupper($this->faker->lexify('M???-###')),
            'description' => $this->faker->sentence(6),
            'credits' => 0,
        ];
    }
}

