<?php

namespace Tests\Feature\Hive;

use App\Models\Gradable;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Hive\Traits\CreatesAssessmentFixture;

class SubmissionControllerTest extends HiveTestCase
{
    use CreatesAssessmentFixture;

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

    public function test_file_exceeding_max_size_is_rejected(): void
    {
        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'max_file_size' => 50,
            'allowed_types' => 'pdf,doc,docx',
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('submission.pdf', 100);

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('submissions', [
            'gradable_id' => $gradable->id,
            'student_id' => $fixture['student1']->id,
        ]);
    }

    public function test_file_with_disallowed_extension_is_rejected(): void
    {
        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'max_file_size' => null,
            'allowed_types' => 'pdf',
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('submission.docx', 100);

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('submissions', [
            'gradable_id' => $gradable->id,
            'student_id' => $fixture['student1']->id,
        ]);
    }

    public function test_valid_file_is_accepted(): void
    {
        Notification::fake();

        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'max_file_size' => 1000,
            'allowed_types' => 'pdf,doc,docx',
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('submission.pdf', 100);

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('submissions', [
            'gradable_id' => $gradable->id,
            'student_id' => $fixture['student1']->id,
        ]);
    }

    public function test_resubmission_replaces_prior_file(): void
    {
        Notification::fake();

        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'max_file_size' => 1000,
            'allowed_types' => 'pdf,doc,docx',
        ]);

        Storage::fake('local');
        $firstFile = UploadedFile::fake()->create('first.pdf', 100);
        $secondFile = UploadedFile::fake()->create('second.pdf', 100);

        $this->post(route('hive.submissions.store', $gradable), [
            'file' => $firstFile,
        ]);

        $firstPath = \App\Models\Submission::where('gradable_id', $gradable->id)
            ->where('student_id', $fixture['student1']->id)
            ->value('file_path');

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $secondFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('submissions', 1);
        $this->assertDatabaseHas('submissions', [
            'gradable_id' => $gradable->id,
            'student_id' => $fixture['student1']->id,
        ]);

        $submission = \App\Models\Submission::where('gradable_id', $gradable->id)
            ->where('student_id', $fixture['student1']->id)
            ->first();
        $this->assertNotNull($submission);
        $this->assertNotEquals($firstPath, $submission->file_path);
    }

    public function test_resubmission_does_not_delete_old_file(): void
    {
        Notification::fake();

        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'max_file_size' => 1000,
            'allowed_types' => 'pdf,doc,docx',
        ]);

        Storage::fake('local');
        $firstFile = UploadedFile::fake()->create('first.pdf', 100);
        $secondFile = UploadedFile::fake()->create('second.pdf', 100);

        $this->post(route('hive.submissions.store', $gradable), [
            'file' => $firstFile,
        ]);

        $firstPath = \App\Models\Submission::where('gradable_id', $gradable->id)
            ->where('student_id', $fixture['student1']->id)
            ->value('file_path');

        $this->post(route('hive.submissions.store', $gradable), [
            'file' => $secondFile,
        ]);

        $this->assertDatabaseCount('submissions', 1);

        $this->assertFalse(Storage::exists($firstPath), 'Old file should be deleted on resubmission');
    }

    public function test_submission_after_due_date_is_blocked(): void
    {
        Notification::fake();

        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'due_date' => now()->subDay(),
            'max_file_size' => 1000,
            'allowed_types' => 'pdf,doc,docx',
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('submission.pdf', 100);

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('submissions', [
            'gradable_id' => $gradable->id,
            'student_id' => $fixture['student1']->id,
        ]);
    }

    public function test_submission_before_due_date_is_marked_on_time(): void
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

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $submission = \App\Models\Submission::where('gradable_id', $gradable->id)
            ->where('student_id', $fixture['student1']->id)
            ->first();
        $this->assertNotNull($submission);
        $this->assertFalse($submission->is_late);
    }

    public function test_is_late_flag_is_never_set_due_to_policy_boundary(): void
    {
        Notification::fake();

        $fixture = $this->createAssessmentFixture();
        $this->actingAs($fixture['student1']);

        $gradable = $fixture['gradable'];
        $gradable->update([
            'due_date' => now()->subMinutes(1),
            'max_file_size' => 1000,
            'allowed_types' => 'pdf,doc,docx',
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('submission.pdf', 100);

        $response = $this->post(route('hive.submissions.store', $gradable), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('submissions', [
            'gradable_id' => $gradable->id,
            'student_id' => $fixture['student1']->id,
        ]);
    }
}
