<?php

namespace Tests\Feature\Hive;

use App\Models\Module;
use App\Models\Submission;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Hive\Traits\CreatesAssessmentFixture;

class GradeControllerTest extends HiveTestCase
{
    use CreatesAssessmentFixture;

    public function test_grades_index_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.index'));

        $response->assertOk();
    }

    public function test_grades_manage_returns_success_for_staff(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $module = Module::factory()->create();
        $module->instructors()->attach($user->id);

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.manage', $module));

        $response->assertOk();
    }

    public function test_grades_manage_returns_403_for_unauthorized(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.grades.manage', $module));

        $response->assertRedirect();
    }

    public function test_student_sees_only_own_grades_in_index(): void
    {
        Notification::fake();

        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'due_date' => now()->addDay(),
            'max_file_size' => 1000,
            'allowed_types' => 'pdf,doc,docx',
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('submission.pdf', 100);

        $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $submission = Submission::where('gradable_id', $gradable->id)
            ->where('student_id', $fixture['student1']->id)
            ->first();
        $submission->update(['grade' => 85, 'feedback' => 'Good work', 'graded_at' => now()]);

        $response = $this->get(route('hive.grades.index'));

        $response->assertOk();

        $props = $this->getInertiaProps($response);
        $modules = $props['modules'] ?? [];
        $student1Module = collect($modules)->firstWhere('id', $fixture['module']->id);
        $this->assertNotNull($student1Module, 'Student 1 should see their enrolled module');

        $gradables = $student1Module['gradables'] ?? [];
        $this->assertNotEmpty($gradables);

        $submissionData = collect($gradables)->firstWhere('id', $gradable->id)['submission'] ?? null;
        $this->assertNotNull($submissionData);
        $this->assertSame('85.00', $submissionData['grade']);
        $this->assertSame($fixture['student1']->id, $submissionData['student_id']);
    }

    public function test_student_does_not_see_classmates_grades(): void
    {
        Notification::fake();

        $fixture = $this->createAssessmentFixture();

        $fixture['student2']->modules()->attach($fixture['module']->id);
        Enrollment::create([
            'user_id' => $fixture['student2']->id,
            'module_id' => $fixture['module']->id,
            'academic_year' => now()->format('Y'),
            'semester' => now()->month <= 6 ? '1' : '2',
        ]);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'due_date' => now()->addDay(),
            'max_file_size' => 1000,
            'allowed_types' => 'pdf,doc,docx',
        ]);

        Storage::fake('local');
        $student1File = UploadedFile::fake()->create('student1.pdf', 100);
        $student2File = UploadedFile::fake()->create('student2.pdf', 100);

        $this->actingAs($fixture['student1']);
        $this->post(route('hive.submissions.store', $gradable), ['file' => $student1File]);

        $this->actingAs($fixture['student2']);
        $this->post(route('hive.submissions.store', $gradable), ['file' => $student2File]);

        $student1Submission = Submission::where('gradable_id', $gradable->id)
            ->where('student_id', $fixture['student1']->id)
            ->first();
        $student1Submission->update(['grade' => 90, 'feedback' => 'Excellent', 'graded_at' => now()]);

        $student2Submission = Submission::where('gradable_id', $gradable->id)
            ->where('student_id', $fixture['student2']->id)
            ->first();
        $student2Submission->update(['grade' => 75, 'feedback' => 'Good', 'graded_at' => now()]);

        $this->actingAs($fixture['student2']);

        $response = $this->get(route('hive.grades.index'));

        $response->assertOk();

        $props = $this->getInertiaProps($response);
        $modules = $props['modules'] ?? [];
        $student2Module = collect($modules)->firstWhere('id', $fixture['module']->id);
        $this->assertNotNull($student2Module, 'Student 2 should see their enrolled module');

        $gradables = $student2Module['gradables'] ?? [];
        $this->assertNotEmpty($gradables);

        $student2SubmissionData = collect($gradables)->firstWhere('id', $gradable->id)['submission'] ?? null;
        $this->assertNotNull($student2SubmissionData);
        $this->assertSame('75.00', $student2SubmissionData['grade']);
        $this->assertSame($fixture['student2']->id, $student2SubmissionData['student_id']);

        $student1SubmissionData = collect($gradables)->firstWhere('id', $gradable->id)['submission'] ?? null;
        $this->assertNotNull($student1SubmissionData);
        $this->assertNotEquals('90.00', $student1SubmissionData['grade']);
    }

    private function getInertiaProps($response): array
    {
        $content = $response->getContent();
        preg_match('/data-page="([^"]+)"/', $content, $matches);
        
        if (!isset($matches[1])) {
            return [];
        }

        $json = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $data = json_decode($json, true);
        
        return $data['props'] ?? [];
    }
}
