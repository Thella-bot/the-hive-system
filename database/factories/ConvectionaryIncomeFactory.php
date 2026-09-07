<?php

namespace Database\Factories;

use App\Models\ConvectionaryIncome;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConvectionaryIncomeFactory extends Factory
{
    protected $model = ConvectionaryIncome::class;

    public function definition(): array
    {
        $source = array_key_first(ConvectionaryIncome::SOURCES);
        $method = array_key_first(ConvectionaryIncome::METHODS);

        return [
            'reference' => 'CI-' . now()->format('Y') . '-' . strtoupper($this->faker->unique()->bothify('##??##')),
            'source' => $source,
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'income_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'description' => $this->faker->sentence(),
            'payment_method' => $method,
            'status' => 'received',
            'recorded_by' => User::factory(),
        ];
    }
}