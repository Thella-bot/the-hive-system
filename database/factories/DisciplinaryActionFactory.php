<?php

namespace Database\Factories;

use App\Models\DisciplinaryAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisciplinaryActionFactory extends Factory
{
    protected $model = DisciplinaryAction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['warning', 'suspension', 'expulsion']),
            'warning_level' => $this->faker->optional()->randomElement(['first', 'second', 'final']),
            'offence' => $this->faker->sentence(6),
            'incident_description' => $this->faker->paragraph(),
            'hearing_date' => $this->faker->date(),
            'effective_date' => $this->faker->date(),
            'duration' => $this->faker->optional()->randomElement(['1 week', '2 weeks', '1 month']),
            'return_date' => $this->faker->optional()->date(),
            'campus_access' => $this->faker->optional()->randomElement(['Full access', 'Prohibited']),
            'surrender_date' => $this->faker->optional()->date(),
            'review_date' => $this->faker->optional()->date(),
            'grounds' => $this->faker->optional()->randomElements(['Late submission', 'Misconduct', 'Violation of Policy'], 2),
            'policy_violated' => $this->faker->optional()->sentence(),
            'corrective_actions' => $this->faker->optional()->randomElements(['Attend workshop', 'Write apology letter'], 2),
            'advisor_name' => $this->faker->optional()->name(),
            'hr_rep' => $this->faker->optional()->name(),
            'expiry_date' => $this->faker->optional()->date(),
            'status' => $this->faker->optional()->randomElement(['active', 'expired', 'appealed']),
        ];
    }
}
