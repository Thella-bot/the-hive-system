<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Gradable;
use App\Models\Module;
use App\Models\Submission;
use App\Models\User;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionAndDocumentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_grade_a_submission_of_another_student(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        Storage::fake('local');

        $studentA = User::factory()->create();
        $studentB = User::factory()->create();
        $instructor = User::factory()->create();

        $studentA->assignRole('student');
        $studentB->assignRole('student');
        $instructor->assignRole('instructor');


        $programme = \App\Models\Programme::create([
            'name' => 'Test Programme',
            'description' => 'Test',
            'duration_years' => 1,
        ]);

        $module = Module::create([
            'programme_id' => $programme->id,
            'name' => 'Test Module',
            'code' => 'TM-001',
            'description' => 'Test',
            'credits' => 0,
        ]);


        // student-module pivot (enrollments requires academic_year + semester)
        if (method_exists($module, 'students')) {
            $module->students()->attach([
                $studentA->id => ['academic_year' => '2026', 'semester' => '1'],
                $studentB->id => ['academic_year' => '2026', 'semester' => '1'],
            ]);
        }


        $gradable = Gradable::create([
            'module_id' => $module->id,
            'instructor_id' => $instructor->id,
            'title' => 'Test Assignment',
            'description' => 'Test',
            'due_date' => now()->addDay(),
            'max_file_size' => 10,
        ]);


        $submission = Submission::create([
            'gradable_id' => $gradable->id,
            'student_id' => $studentB->id,
            'file_path' => 'private/submissions/dummy.pdf',
            'submitted_at' => now(),
            'is_late' => false,
        ]);

        $this->actingAs($studentA);

        $response = $this->post(route('hive.submissions.grade', ['submission' => $submission->id]), [

            'grade' => 80,
            'feedback' => 'ok',
        ]);

        $response->assertStatus(403);
    }

    public function test_document_version_download_is_authorized_per_document_policy(): void
    {
        $this->seed(
            \Database\Seeders\RoleSeeder::class
        );

        Storage::fake('local');

        $admin = User::factory()->create();
        $admin->assignRole('admin');


        $instructor = User::factory()->create();
        $instructor->assignRole('instructor');

        $document = Document::create([
            'title' => 'Instructor Handbook',
            'category' => 'general',
            'is_published' => true,
            'visible_to_roles' => ['instructor'],
            'created_by' => $instructor->id,
        ]);

        $pathV1 = 'private/documents/' . $document->id . '/v1.pdf';
        Storage::disk('local')->put($pathV1, 'v1');

        $version1 = DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => 1,
            'file_path' => $pathV1,
            'uploaded_by' => $instructor->id,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('hive.documents.version.download', ['version' => $version1->id]));
        $response->assertStatus(200);

        // user without the allowed role should be blocked
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student);
        $response2 = $this->get(route('hive.documents.version.download', ['version' => $version1->id]));
        $response2->assertStatus(403);
    }
}