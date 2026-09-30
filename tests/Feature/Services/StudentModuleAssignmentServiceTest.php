<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Module;
use App\Models\Programme;
use App\Models\User;
use App\Services\StudentModuleAssignmentService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentModuleAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Programme $programme;

    protected Module $first;

    protected Module $second;

    protected Module $foreign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => RolePermissionSeeder::class]);

        $this->programme = Programme::factory()->create();

        $this->first = Module::factory()->create();
        $this->second = Module::factory()->create();
        $this->foreign = Module::factory()->create();

        $this->programme->modules()->attach($this->first, ['year_level' => 1, 'semester' => 1]);
        $this->programme->modules()->attach($this->second, ['year_level' => 1, 'semester' => 2]);
    }

    public function test_it_gives_the_student_exactly_the_programme_modules(): void
    {
        $student = $this->student();

        $report = app(StudentModuleAssignmentService::class)->sync($student, $this->programme);

        $this->assertEqualsCanonicalizing(
            [$this->first->id, $this->second->id],
            $student->modules()->pluck('modules.id')->all(),
        );

        // A student starting with nothing has both programme modules added.
        $this->assertEqualsCanonicalizing([$this->first->id, $this->second->id], $report['added']);
        $this->assertEmpty($report['removed']);
    }

    public function test_it_removes_modules_from_another_programme(): void
    {
        $student = $this->student();
        $student->modules()->sync([$this->foreign->id]);

        $report = app(StudentModuleAssignmentService::class)->sync($student, $this->programme);

        $this->assertSame([$this->foreign->id], $report['removed']);
        $this->assertEqualsCanonicalizing(
            [$this->first->id, $this->second->id],
            $student->modules()->pluck('modules.id')->all(),
        );
    }

    public function test_it_adds_modules_missing_from_the_student(): void
    {
        $student = $this->student();
        $student->modules()->sync([$this->first->id]);

        $report = app(StudentModuleAssignmentService::class)->sync($student, $this->programme);

        $this->assertSame([$this->second->id], $report['added']);
        $this->assertCount(2, $student->modules()->get());
    }

    public function test_a_student_with_no_programme_ends_up_with_no_modules(): void
    {
        $student = $this->student();
        $student->modules()->sync([$this->first->id, $this->foreign->id]);

        app(StudentModuleAssignmentService::class)->sync($student, null);

        $this->assertCount(0, $student->modules()->get());
    }

    public function test_it_is_idempotent(): void
    {
        $student = $this->student();
        $service = app(StudentModuleAssignmentService::class);

        $service->sync($student, $this->programme);
        $second = $service->sync($student, $this->programme);

        $this->assertEmpty($second['added']);
        $this->assertEmpty($second['removed']);
        $this->assertCount(2, $student->modules()->get());
    }

    public function test_a_module_shared_by_two_programmes_stays_assigned(): void
    {
        $other = Programme::factory()->create();
        $other->modules()->attach($this->first);

        $student = $this->student();
        app(StudentModuleAssignmentService::class)->sync($student, $other);

        $this->assertEqualsCanonicalizing(
            [$this->first->id],
            $student->modules()->pluck('modules.id')->all(),
        );

        app(StudentModuleAssignmentService::class)->sync($student, $this->programme);

        $this->assertEqualsCanonicalizing(
            [$this->first->id, $this->second->id],
            $student->modules()->pluck('modules.id')->all(),
        );
    }

    public function test_inspect_reports_drift_without_writing(): void
    {
        $student = $this->student();
        $student->modules()->sync([$this->foreign->id]);

        $report = app(StudentModuleAssignmentService::class)->inspect($student, $this->programme);

        $this->assertFalse($report['in_sync']);
        $this->assertSame(2, $report['expected']);
        $this->assertSame(1, $report['actual']);
        $this->assertSame([$this->foreign->id], $report['removed']);

        // Nothing written.
        $this->assertSame([$this->foreign->id], $student->modules()->pluck('modules.id')->all());
    }

    public function test_inspect_reports_a_matched_student_as_in_sync(): void
    {
        $student = $this->student();
        $service = app(StudentModuleAssignmentService::class);
        $service->sync($student, $this->programme);

        $this->assertTrue($service->inspect($student, $this->programme)['in_sync']);
    }

    protected function student(): User
    {
        $user = User::factory()->create(['programme_id' => $this->programme->id]);
        $user->assignRole('student');

        return $user;
    }
}
