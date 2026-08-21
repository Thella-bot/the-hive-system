<?php

namespace Tests\Feature\Hive;

use App\Models\Gradable;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class SubmissionControllerTest extends HiveTestCase
{
    public function test_submission_store_requires_registered_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $gradable = Gradable::factory()->create();

        Storage::fake('local');

        $file = UploadedFile::fake()->create('submission.pdf', 100);

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
    }

    public function test_submission_grade_marks_submission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $gradable = Gradable::factory()->create();
        $student = User::factory()->create()->assignRole('student');
        $submission = \App\Models\Submission::factory()->create([
            'gradable_id' => $gradable->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.submissions.grade', $submission), [
            'grade' => 85.5,
            'feedback' => 'Well done',
        ]);

        $response->assertRedirect();
    }

    public function test_submission_download_redirects(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $submission = \App\Models\Submission::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.submissions.download', $submission));

        $response->assertRedirect();
    }

    public function test_student_not_enrolled_cannot_submit_gradable(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();
        $gradable = Gradable::factory()->create([
            'module_id' => $module->id,
            'due_date' => now()->addDays(7),
        ]);

        $this->actingAs($user);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('submission.pdf', 100);

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('submissions', [
            'gradable_id' => $gradable->id,
            'student_id' => $user->id,
        ]);
    }

    public function test_student_enrolled_can_submit_gradable(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->assignRole('student');

        $module = Module::factory()->create();
        $gradable = Gradable::factory()->create([
            'module_id' => $module->id,
            'due_date' => now()->addDays(7),
        ]);

        $user->modules()->attach($module->id);

        $this->actingAs($user);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('submission.pdf', 100);

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('submissions', [
            'gradable_id' => $gradable->id,
            'student_id' => $user->id,
        ]);
    }
}
