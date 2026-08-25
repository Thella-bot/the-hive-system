<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'expense_category_id' => ExpenseCategory::factory(),
            'user_id' => User::factory(),
            'vendor_id' => Supplier::factory(),
            'budget_id' => Budget::factory(),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 50, 5000),
            'expense_date' => $this->faker->date(),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'mobile_money', 'cheque']),
            'reference_number' => $this->faker->optional()->numerify('REF-########'),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'paid', 'cancelled']),
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->optional()->date(),
            'receipt_path' => $this->faker->optional()->filePath(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
