<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'programme_id' => Programme::factory(),
            'academic_year' => now()->year . '/' . (now()->year + 1),
            'semester' => $this->faker->numberBetween(1, 3),
            'type' => $this->faker->randomElement(['registration', 'tuition', 'uniform', 'tools', 'resource', 'examination', 'other']),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'due_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['pending', 'partial', 'paid', 'overdue', 'cancelled']),
            'notes' => $this->faker->sentence(),
        ];
    }
}
