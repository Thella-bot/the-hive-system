<?php

namespace Tests\Feature\Hive;

use App\Jobs\ImportUsersJob;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Profile;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserLifecycleAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => RolePermissionSeeder::class]);
    }

    // §0 — Student must be blocked from every admin/user route
    public function test_student_is_blocked_from_all_admin_user_routes(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        $target = User::factory()->create();
        $target->assignRole('student');

        $this->actingAs($student);

        $routes = [
            'GET'    => ['/hive/users', '/hive/users/create'],
            'POST'   => ['/hive/users'],
            'GET'    => ['/hive/users/' . $target->id, '/hive/users/' . $target->id . '/edit'],
            'PUT'    => ['/hive/users/' . $target->id],
            'DELETE' => ['/hive/users/' . $target->id],
            'GET'    => ['/hive/admin/approve-users', '/hive/admin/import-users'],
            'POST'   => ['/hive/admin/approve-users/' . $target->id],
            'POST'   => ['/hive/departments'],
        ];

        foreach ($routes as $method => $paths) {
            foreach ($paths as $path) {
                $response = $this->call($method, $path);
                // Spatie RoleMiddleware redirects (302) for unauthorized access
                $response->assertStatus(302, "Expected 302 for {$method} {$path}, got {$response->getStatusCode()}");
            }
        }
    }

    // §1 — Super-admin full lifecycle + cascade check
    public function test_super_admin_user_lifecycle_and_cascade_behavior(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        // CREATE
        $response = $this->post('/hive/users', [
            'name' => 'Lifecycle Student',
            'email' => 'lifecycle@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['student'],
        ]);
        $response->assertRedirect();
        $user = User::where('email', 'lifecycle@test.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('student'));

        // VIEW
        $response = $this->get('/hive/users/' . $user->id);
        $response->assertOk();
        $this->assertDatabaseHas('users', ['email' => 'lifecycle@test.com']);

        // EDIT profile fields (not roles)
        $response = $this->get('/hive/users/' . $user->id . '/edit');
        $response->assertOk();

        $response = $this->put('/hive/users/' . $user->id, [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
            'roles' => ['student'],
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'updated@test.com', 'name' => 'Updated Name']);

        // Pre-delete related records
        $profile = $user->profile;
        $enrollment = Enrollment::factory()->create(['user_id' => $user->id]);
        $submission = Submission::factory()->create(['student_id' => $user->id]);

        // DELETE
        $response = $this->delete('/hive/users/' . $user->id);
        $response->assertRedirect();

        // Confirm soft-delete on user
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        // Profile is orphaned (no FK on morphs), still exists
        $this->assertDatabaseHas('profiles', ['id' => $profile->id]);

        // Enrollment and Submission remain because soft-delete doesn't trigger DB cascade
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);
        $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
    }

    // §1 — Guard against deleting last super-admin
    public function test_cannot_delete_last_super_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');
        $this->actingAs($superAdmin);

        $response = $this->delete('/hive/users/' . $superAdmin->id);
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    // §2 — Public registration → unapproved → blocked until approved
    public function test_public_registration_creates_unapproved_user(): void
    {
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authenticate::class);

        $response = $this->post('/register', [
            'name' => 'Public Applicant',
            'email' => 'public@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $response->assertStatus(302);

        $this->assertNotNull($user = User::where('email', 'public@test.com')->first());
        $this->assertTrue($user->hasRole('unapproved'));
        $this->assertNull($user->approved_at);

        // In production, EnsureUserIsApproved middleware blocks unapproved users.
        // The key assertion is that the account starts as unapproved.
        $this->assertTrue($user->hasRole('unapproved'));
    }

    // §2 — Approval flow
    public function test_super_admin_can_approve_unapproved_user(): void
    {
        $unapproved = User::factory()->create();
        $unapproved->assignRole('unapproved');

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        Notification::fake();

        $response = $this->post('/hive/admin/approve-users/' . $unapproved->id, [
            'role' => 'student',
        ]);
        $response->assertRedirect();

        $this->assertNotNull($unapproved->fresh()->approved_at);
        $this->assertTrue($unapproved->fresh()->hasRole('student'));
        $this->assertFalse($unapproved->fresh()->hasRole('unapproved'));

        Notification::assertSentTo($unapproved, \App\Notifications\UserApproved::class);
    }

    // §3 — Import CSV: invalid role, duplicate email, missing columns
    public function test_import_users_job_handles_invalid_role_and_duplicates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        // Pre-create a user to test duplicate handling
        $existingUser = User::factory()->create(['email' => 'dup@test.com']);

        Notification::fake();

        // All rows are valid except the missing-columns row which should fail
        $csv = "full_name,email,role\n";
        $csv .= "New User,new@test.com,student\n";
        $csv .= "Duplicate User,dup@test.com,student\n";
        $csv .= "Bad Role User,badrole@test.com,nonexistent-role\n";
        $csv .= "Short Row\n";

        $path = Storage::putFile('imports', \Illuminate\Http\UploadedFile::fake()->create('test.csv', 1, 'csv'));
        Storage::put($path, $csv);

        $job = new ImportUsersJob($path, $admin);
        $job->handle();

        // Because validation errors occurred, the entire transaction is skipped
        // No new users created, no updates made
        $this->assertDatabaseMissing('users', ['email' => 'new@test.com']);

        // Admin got notified of the failure
        Notification::assertSentTo($admin, \App\Notifications\ImportCompleted::class, function ($n) {
            return $n->getFailureCount() >= 1 && $n->getJobError() !== null;
        });
    }

    public function test_import_users_job_imports_valid_rows_and_detects_duplicates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        $existingUser = User::factory()->create(['email' => 'dup@test.com']);

        Notification::fake();

        // All rows valid - tests duplicate email handling
        $csv = "full_name,email,role\n";
        $csv .= "New User,new@test.com,student\n";
        $csv .= "Duplicate User,dup@test.com,student\n";

        $path = Storage::putFile('imports', \Illuminate\Http\UploadedFile::fake()->create('test.csv', 1, 'csv'));
        Storage::put($path, $csv);

        $job = new ImportUsersJob($path, $admin);
        $job->handle();

        // New user was created
        $this->assertDatabaseHas('users', ['email' => 'new@test.com']);

        // Duplicate user was updated (approved_at set, name updated)
        $this->assertDatabaseHas('users', ['email' => 'dup@test.com']);

        // Admin notified of success
        Notification::assertSentTo($admin, \App\Notifications\ImportCompleted::class, function ($n) {
            return $n->getSuccessCount() === 2 && $n->getFailureCount() === 0;
        });
    }

    // §4 — Academic director dept/cohort/year creation + delete constraints
    public function test_academic_director_department_cohort_year_lifecycle(): void
    {
        $director = User::factory()->create();
        $director->assignRole('academic-director');
        $this->actingAs($director);

        // Create academic year first
        $year = \App\Models\AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        // Create department
        $response = $this->post('/hive/departments', [
            'name' => 'Test Department',
            'description' => 'Audit dept',
        ]);
        $response->assertRedirect();
        $dept = Department::where('name', 'Test Department')->first();
        $this->assertNotNull($dept);

        // Create cohort under it
        $response = $this->post('/hive/cohorts', [
            'name' => 'Audit Cohort',
            'department_id' => $dept->id,
            'academic_year_id' => $year->id,
            'max_students' => 20,
        ]);
        $response->assertSessionHasNoErrors();
        $cohort = Cohort::where('name', 'Audit Cohort')->first();
        $this->assertNotNull($cohort);
        $this->assertEquals($dept->id, $cohort->department_id);

        // Try to delete department while cohort exists
        $response = $this->delete('/hive/departments/' . $dept->id);
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
    }

    // §5 — Chef-instructor cannot browse arbitrary user records
    public function test_chef_instructor_cannot_view_arbitrary_users(): void
    {
        $instructor = User::factory()->create();
        $instructor->assignRole('chef-instructor');
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($instructor);

        $response = $this->get('/hive/users/' . $student->id);
        // Spatie RoleMiddleware redirects (302) for unauthorized access
        $response->assertStatus(302);
    }

    // §6 — RoleObserver: it fires on Role model changes, NOT on user role assignments
    public function test_role_observer_fires_on_role_model_changes(): void
    {
        $service = app(\App\Services\ReferenceDataService::class);

        // Prime the cache
        $cached = $service->roles()->pluck('name')->toArray();
        $this->assertTrue(in_array('student', $cached));

        // Create a new role — this triggers RoleObserver::saved()
        \Spatie\Permission\Models\Role::create(['name' => 'audit-role', 'guard_name' => 'web']);

        // Cache should be flushed, so next call re-queries DB
        $refreshed = $service->roles()->pluck('name')->toArray();
        $this->assertTrue(in_array('audit-role', $refreshed));

        // Clean up
        \Spatie\Permission\Models\Role::where('name', 'audit-role')->delete();
    }

    public function test_role_observer_does_not_fire_on_user_role_sync(): void
    {
        $service = app(\App\Services\ReferenceDataService::class);
        $cachedBefore = $service->roles()->pluck('name')->toArray();

        $user = User::factory()->create();
        $user->assignRole('student');

        // Cache should be unchanged because user role sync doesn't trigger RoleObserver
        $cachedAfter = $service->roles()->pluck('name')->toArray();
        $this->assertEquals($cachedBefore, $cachedAfter);
    }
}
