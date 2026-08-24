<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->year() . '/' . ($this->faker->unique()->year() + 1),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'is_current' => false,
        ];
    }
}
