<?php

namespace Database\Factories;

use App\Models\Gradable;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'gradable_id' => Gradable::factory(),
            'student_id' => User::factory(),
            'file_path' => 'private/submissions/' . fake()->uuid() . '.pdf',
            'submitted_at' => now(),
            'is_late' => false,
        ];
    }
}
