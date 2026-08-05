<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 2000),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'mobile_money', 'card', 'other']),
            'payment_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'notes' => $this->faker->sentence(),
        ];
    }
}
