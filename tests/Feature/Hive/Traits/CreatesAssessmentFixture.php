<?php

namespace Tests\Feature\Hive\Traits;

use App\Models\Enrollment;
use App\Models\Gradable;
use App\Models\Module;
use App\Models\User;

trait CreatesAssessmentFixture
{
    public function createAssessmentFixture(): array
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('chef-instructor');

        $module = Module::factory()->create();
        $module->instructors()->attach($instructor->id);

        $student1 = User::factory()->create();
        $student1->assignRole('student');

        $student2 = User::factory()->create();
        $student2->assignRole('student');

        Enrollment::create([
            'user_id' => $student1->id,
            'module_id' => $module->id,
            'academic_year' => now()->format('Y'),
            'semester' => now()->month <= 6 ? '1' : '2',
        ]);

        $student1->modules()->attach($module->id);

        $gradable = Gradable::create([
            'type' => 'assignment',
            'submission_type' => 'file_upload',
            'module_id' => $module->id,
            'instructor_id' => $instructor->id,
            'title' => 'Test Assignment',
            'description' => 'Assignment for testing',
            'due_date' => now()->addDays(7),
            'max_marks' => 100,
            'weight' => 20,
        ]);

        return [
            'instructor' => $instructor,
            'module' => $module,
            'student1' => $student1,
            'student2' => $student2,
            'gradable' => $gradable,
        ];
    }
}
