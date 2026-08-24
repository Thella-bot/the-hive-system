<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class CohortFactory extends Factory
{
    protected $model = Cohort::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' Cohort',
            'slug' => null,
            'department_id' => Department::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'max_students' => $this->faker->numberBetween(10, 50),
            'is_active' => true,
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
        ];
    }
}
