<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'category' => $this->faker->randomElement(['food', 'equipment', 'supplies', 'maintenance', 'services', 'other']),
            'contact_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'contract_expiry' => $this->faker->optional()->date('Y-m-d', '+2 years'),
            'notes' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
