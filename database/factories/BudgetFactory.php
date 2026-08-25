<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Department;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'academic_year' => now()->year . '/' . (now()->year + 1),
            'semester' => $this->faker->numberBetween(1, 2),
            'department_id' => Department::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'approved_budget' => $this->faker->randomFloat(2, 5000, 50000),
            'allocated_amount' => $this->faker->randomFloat(2, 5000, 50000),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['draft', 'active', 'closed']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
