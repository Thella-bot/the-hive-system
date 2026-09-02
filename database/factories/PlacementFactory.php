<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Placement;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlacementFactory extends Factory
{
    protected $model = Placement::class;

    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'programme_id' => Programme::factory(),
            'organisation_name' => $this->faker->company(),
            'organisation_address' => $this->faker->address(),
            'supervisor_name' => $this->faker->name(),
            'supervisor_contact' => $this->faker->phoneNumber(),
            'start_date' => $startDate = $this->faker->date(),
            'end_date' => $this->faker->dateTimeBetween($startDate, '+1 year')->format('Y-m-d'),
            'duration' => $this->faker->randomElement(['3 months', '6 months', '12 months']),
            'type' => $this->faker->randomElement(['Compulsory', 'Elective']),
            'status' => $this->faker->randomElement(['pending', 'active', 'completed', 'cancelled']),
            'learning_objectives' => $this->faker->paragraph(),
        ];
    }
}
