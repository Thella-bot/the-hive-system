<?php

declare(strict_types=1);

namespace Tests\Feature\Hive;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\EnrollmentRequest;
use App\Models\Module;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class EnrollmentRequestWorkflowTest extends HiveTestCase
{
    use RefreshDatabase;

    /**
     * Mirrors the semester the controller derives from the current date, so the
     * fixture module always sits in the term the student is actually offered.
     */
    private function currentSemester(): string
    {
        return now()->month <= 6 ? '1' : '2';
    }

    private function programmeWithModule(int $yearLevel = 1, string $semester = '1'): array
    {
        $year = AcademicYear::create([
            'name' => '2026',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_current' => true,
        ]);

        $programme = Programme::factory()->create();
        $module = Module::factory()->create();

        $programme->modules()->attach($module->id, [
            'year_level' => $yearLevel,
            'semester' => $semester,
            'order_column' => 1,
        ]);

        return [$year, $programme, $module];
    }

    private function studentOnProgramme(Programme $programme, $year): User
    {
        // The controller derives semester/year level from the student's cohort,
        // so the fixture needs a real cohort in the current academic year.
        $cohort = Cohort::create([
            'name' => 'Test Cohort ' . uniqid(),
            'slug' => 'test-cohort-' . uniqid(),
            'department_id' => $programme->department_id,
            'academic_year_id' => $year->id,
            'max_students' => 50,
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');
        $user->programme_id = $programme->id;
        $user->student_number = 'S' . random_int(1000000, 9999999);
        $user->save();

        $user->profile()->create([
            'first_name' => $user->name,
            'student_number' => $user->student_number,
            'cohort_id' => $cohort->id,
        ]);

        return $user;
    }

    private function reviewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $user->givePermissionTo('view-enrollments');

        return $user;
    }

    public function test_student_enrollment_creates_a_pending_request_not_an_enrollment(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);

        $this->actingAs($student)
            ->post(route('hive.enrollment.store'), ['module_id' => $module->id])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_requests', [
            'user_id' => $student->id,
            'module_id' => $module->id,
            'type' => EnrollmentRequest::TYPE_ENROLLMENT,
            'status' => EnrollmentRequest::STATUS_PENDING,
        ]);

        // Crucially, no real enrollment exists until it is approved.
        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $student->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_duplicate_pending_enrollment_request_is_blocked(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);

        $this->actingAs($student)
            ->post(route('hive.enrollment.store'), ['module_id' => $module->id])
            ->assertRedirect();

        $this->actingAs($student)
            ->post(route('hive.enrollment.store'), ['module_id' => $module->id])
            ->assertRedirect();

        $this->assertSame(
            1,
            EnrollmentRequest::where('user_id', $student->id)
                ->where('module_id', $module->id)
                ->where('status', EnrollmentRequest::STATUS_PENDING)
                ->count()
        );
    }

    public function test_approving_an_enrollment_request_creates_the_enrollment(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);
        $reviewer = $this->reviewer();

        $this->actingAs($student)
            ->post(route('hive.enrollment.store'), ['module_id' => $module->id]);

        $request = EnrollmentRequest::where('user_id', $student->id)->firstOrFail();

        $this->actingAs($reviewer)
            ->patch(route('hive.enrollment.requests.decide', $request), ['status' => 'approved'])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_requests', [
            'id' => $request->id,
            'status' => EnrollmentRequest::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
        ]);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'module_id' => $module->id,
        ]);

        $this->assertTrue($student->fresh()->modules()->where('modules.id', $module->id)->exists());
    }

    public function test_rejecting_an_enrollment_request_creates_no_enrollment(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);
        $reviewer = $this->reviewer();

        $this->actingAs($student)
            ->post(route('hive.enrollment.store'), ['module_id' => $module->id]);

        $request = EnrollmentRequest::where('user_id', $student->id)->firstOrFail();

        $this->actingAs($reviewer)
            ->patch(route('hive.enrollment.requests.decide', $request), [
                'status' => 'rejected',
                'review_note' => 'Not offered this term',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_requests', [
            'id' => $request->id,
            'status' => EnrollmentRequest::STATUS_REJECTED,
            'review_note' => 'Not offered this term',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $student->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_deregistration_request_leaves_enrollment_intact_until_approved(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);
        $reviewer = $this->reviewer();

        Enrollment::create([
            'user_id' => $student->id,
            'module_id' => $module->id,
            'academic_year' => '2026',
            'semester' => '1',
        ]);

        $this->actingAs($student)
            ->delete(route('hive.enrollment.destroy', $module))
            ->assertRedirect();

        $this->assertDatabaseHas('enrollment_requests', [
            'user_id' => $student->id,
            'module_id' => $module->id,
            'type' => EnrollmentRequest::TYPE_DEREGISTRATION,
            'status' => EnrollmentRequest::STATUS_PENDING,
        ]);

        // Still enrolled while the request is pending.
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'module_id' => $module->id,
        ]);

        $request = EnrollmentRequest::where('user_id', $student->id)->firstOrFail();

        $this->actingAs($reviewer)
            ->patch(route('hive.enrollment.requests.decide', $request), ['status' => 'approved'])
            ->assertRedirect();

        // Removed only after approval.
        $this->assertSoftDeleted('enrollments', [
            'user_id' => $student->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_a_student_cannot_decide_their_own_request(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);

        $this->actingAs($student)
            ->post(route('hive.enrollment.store'), ['module_id' => $module->id]);

        $request = EnrollmentRequest::where('user_id', $student->id)->firstOrFail();

        $this->actingAs($student)
            ->patch(route('hive.enrollment.requests.decide', $request), ['status' => 'approved'])
            ->assertForbidden();

        $this->assertDatabaseHas('enrollment_requests', [
            'id' => $request->id,
            'status' => EnrollmentRequest::STATUS_PENDING,
        ]);
    }

    public function test_a_student_cannot_open_the_review_queue(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)
            ->get(route('hive.enrollment.requests'))
            ->assertForbidden();
    }

    public function test_an_already_reviewed_request_cannot_be_decided_again(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);
        $reviewer = $this->reviewer();

        $this->actingAs($student)
            ->post(route('hive.enrollment.store'), ['module_id' => $module->id]);

        $request = EnrollmentRequest::where('user_id', $student->id)->firstOrFail();

        $this->actingAs($reviewer)
            ->patch(route('hive.enrollment.requests.decide', $request), ['status' => 'approved']);

        $this->actingAs($reviewer)
            ->patch(route('hive.enrollment.requests.decide', $request), ['status' => 'rejected'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('enrollment_requests', [
            'id' => $request->id,
            'status' => EnrollmentRequest::STATUS_APPROVED,
        ]);
    }

    public function test_reviewer_sees_the_pending_queue(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);
        $reviewer = $this->reviewer();

        $this->actingAs($student)
            ->post(route('hive.enrollment.store'), ['module_id' => $module->id]);

        $this->actingAs($reviewer)
            ->get(route('hive.enrollment.requests'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Enrollment/Requests')
                ->where('pendingCount', 1));
    }

    public function test_admin_can_still_enroll_directly_without_a_request(): void
    {
        Notification::fake();

        [$year, $programme, $module] = $this->programmeWithModule(1, $this->currentSemester());
        $student = $this->studentOnProgramme($programme, $year);
        $reviewer = $this->reviewer();

        $this->actingAs($reviewer)
            ->post(route('hive.enrollment.admin.store'), [
                'user_id' => $student->id,
                'module_id' => $module->id,
                'academic_year' => '2026',
                'semester' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'module_id' => $module->id,
        ]);

        $this->assertDatabaseCount('enrollment_requests', 0);
    }
}
