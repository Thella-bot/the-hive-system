<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Programme;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncStudentModulesCommandTest extends TestCase
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

    public function test_it_syncs_a_drifted_student(): void
    {
        $student = $this->student($this->programme);
        $student->modules()->sync([$this->foreign->id]);

        $this->artisan('students:sync-modules')->assertSuccessful();

        $this->assertEqualsCanonicalizing(
            [$this->first->id, $this->second->id],
            $student->modules()->pluck('modules.id')->all(),
        );
    }

    public function test_a_dry_run_writes_nothing(): void
    {
        $student = $this->student($this->programme);
        $student->modules()->sync([$this->foreign->id]);

        $this->artisan('students:sync-modules', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame([$this->foreign->id], $student->modules()->pluck('modules.id')->all());
    }

    public function test_it_can_target_a_single_student_by_number(): void
    {
        $drifted = $this->student($this->programme);
        $drifted->modules()->sync([$this->foreign->id]);

        $other = $this->student($this->programme);
        $other->modules()->sync([$this->foreign->id]);

        $this->artisan('students:sync-modules', [
            '--student' => $drifted->student_number,
        ])->assertSuccessful();

        $this->assertCount(2, $drifted->modules()->get());
        $this->assertSame([$this->foreign->id], $other->modules()->pluck('modules.id')->all());
    }

    public function test_it_leaves_matched_students_alone(): void
    {
        $student = $this->student($this->programme);
        $student->modules()->sync([$this->first->id, $this->second->id]);

        $this->artisan('students:sync-modules')
            ->expectsOutputToContain('Every student already matches their programme')
            ->assertSuccessful();

        $this->assertCount(2, $student->modules()->get());
    }

    public function test_it_ignores_students_who_are_not_students(): void
    {
        $staff = User::factory()->create(['programme_id' => $this->programme->id]);
        $staff->assignRole('chef-instructor');
        $staff->modules()->sync([$this->foreign->id]);

        // A real student is present so the command has work to do.
        $student = $this->student($this->programme);
        $student->modules()->sync([$this->foreign->id]);

        $this->artisan('students:sync-modules')->assertSuccessful();

        $this->assertCount(2, $student->modules()->get());
        $this->assertSame(
            [$this->foreign->id],
            $staff->modules()->pluck('modules.id')->all(),
            'staff must not be touched',
        );
    }

    public function test_it_fails_when_no_students_match_the_filter(): void
    {
        $this->artisan('students:sync-modules', ['--student' => 'S99999999'])
            ->assertFailed();
    }

    protected function student(Programme $programme): User
    {
        $user = User::factory()->create([
            'programme_id' => $programme->id,
            'student_number' => 'S2026040'.random_int(1, 9),
        ]);
        $user->assignRole('student');

        return $user;
    }
}
