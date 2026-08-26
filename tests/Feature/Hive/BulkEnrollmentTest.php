<?php

namespace Tests\Feature\Hive;

use App\Models\Enrollment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', [
            '--class' => \Database\Seeders\RolePermissionSeeder::class,
        ]);
    }

    public function test_bulk_enrollment_requires_authorization(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('student');

        $module = Module::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.registrar.enrollments.bulk-store'), [
            'module_id' => $module->id,
            'user_ids' => [1, 2, 3],
            'academic_year' => date('Y'),
            'semester' => 1,
        ]);

        // Students are redirected to login due to role middleware
        $response->assertRedirect();
    }

    public function test_bulk_enrollment_enrolls_students(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $module = Module::factory()->create();
        $students = User::factory()->count(3)->create();

        $this->actingAs($user);

        $response = $this->post(route('hive.registrar.enrollments.bulk-store'), [
            'module_id' => $module->id,
            'user_ids' => $students->pluck('id')->toArray(),
            'academic_year' => date('Y'),
            'semester' => 1,
        ]);

        $response->assertRedirect();
        $this->assertCount(3, Enrollment::all());
    }

    public function test_bulk_enrollment_skips_already_enrolled(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $module = Module::factory()->create();
        $students = User::factory()->count(2)->create();

        // Pre-enroll one student
        Enrollment::create([
            'user_id' => $students[0]->id,
            'module_id' => $module->id,
            'academic_year' => date('Y'),
            'semester' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.registrar.enrollments.bulk-store'), [
            'module_id' => $module->id,
            'user_ids' => $students->pluck('id')->toArray(),
            'academic_year' => date('Y'),
            'semester' => 1,
        ]);

        $response->assertRedirect();
        // Should have 2 total enrollments (1 pre-existing + 1 new)
        $this->assertCount(2, Enrollment::all());
    }

    public function test_bulk_destroy_removes_students(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $module = Module::factory()->create();
        $students = User::factory()->count(2)->create();

        // Create enrollments
        foreach ($students as $student) {
            Enrollment::create([
                'user_id' => $student->id,
                'module_id' => $module->id,
                'academic_year' => date('Y'),
                'semester' => 1,
            ]);
        }

        $this->actingAs($user);

        $response = $this->delete(route('hive.registrar.enrollments.bulk-destroy'), [
            'module_id' => $module->id,
            'user_ids' => $students->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $this->assertCount(0, Enrollment::all());
    }

    public function test_bulk_enrollment_validates_required_fields(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $this->actingAs($user);

        $response = $this->post(route('hive.registrar.enrollments.bulk-store'), [
            'module_id' => '',
            'user_ids' => [],
        ]);

        $response->assertSessionHasErrors(['module_id', 'user_ids', 'academic_year', 'semester']);
    }
}
