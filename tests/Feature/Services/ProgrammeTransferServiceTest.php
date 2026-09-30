<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\Module;
use App\Models\Profile;
use App\Models\Programme;
use App\Models\User;
use App\Services\IdGenerator;
use App\Services\ProgrammeTransferService;
use App\Services\StudentNumberService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeTransferServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Department $pastryDepartment;

    protected Department $chefDepartment;

    protected Programme $pastryProgramme;

    protected Programme $chefProgramme;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => RolePermissionSeeder::class]);

        $this->pastryDepartment = Department::factory()->create(['name' => 'Pastry & Bakery']);
        $this->chefDepartment = Department::factory()->create(['name' => 'Culinary Arts']);

        $this->pastryProgramme = Programme::factory()->create([
            'name' => 'Diploma in Culinary Patisserie',
            'department_id' => $this->pastryDepartment->id,
            'duration_months' => 24,
        ]);

        $this->chefProgramme = Programme::factory()->create([
            'name' => 'Diploma in Professional Chef',
            'department_id' => $this->chefDepartment->id,
            'duration_months' => 36,
        ]);
    }

    public function test_it_reissues_the_student_number_when_the_department_changes(): void
    {
        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2));

        $this->transfer($student, $this->chefProgramme);

        $student->refresh();

        $numbers = new StudentNumberService;
        $this->assertSame(
            $this->chefDepartment->id,
            $numbers->departmentIdFor($student->student_number),
            'number should encode the new department',
        );
    }

    public function test_it_keeps_the_users_and_profiles_numbers_in_step(): void
    {
        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2));

        $this->transfer($student, $this->chefProgramme);

        $student->refresh();

        $this->assertSame($student->student_number, $student->profile->student_number);
        $this->assertDatabaseHas('profiles', [
            'id' => $student->profile->id,
            'student_number' => $student->student_number,
        ]);
    }

    public function test_the_reissued_number_keeps_the_enrolment_year(): void
    {
        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2), '2026-08-04');

        $this->transfer($student, $this->chefProgramme);

        $student->refresh();

        $numbers = new StudentNumberService;
        $this->assertSame(2026, $numbers->parse($student->student_number)['year']);
    }

    public function test_it_moves_the_student_to_the_matching_cohort_in_the_new_department(): void
    {
        $academicYear = AcademicYear::factory()->create();

        $oldCohort = Cohort::factory()->create([
            'name' => 'August 2026',
            'department_id' => $this->pastryDepartment->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $newCohort = Cohort::factory()->create([
            'name' => 'August 2026',
            'department_id' => $this->chefDepartment->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2), '2026-08-04', $oldCohort);

        $this->transfer($student, $this->chefProgramme);

        $this->assertSame($newCohort->id, $student->refresh()->profile->cohort_id);
    }

    public function test_it_recomputes_the_graduation_date_from_the_new_duration(): void
    {
        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2), '2026-08-04');

        $this->transfer($student, $this->chefProgramme);

        // 36 months from 2026-08-04, rather than the patisserie 24.
        $this->assertSame('2029-08-04', $student->refresh()->profile->expected_graduation_date->format('Y-m-d'));
    }

    public function test_it_resyncs_the_module_list_to_the_new_programme(): void
    {
        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2));

        $oldModule = Module::factory()->create();
        $newModule = Module::factory()->create();

        Programme::find($this->pastryProgramme->id)->modules()->attach($oldModule);
        Programme::find($this->chefProgramme->id)->modules()->attach($newModule);

        $student->modules()->sync([$oldModule->id]);

        $this->transfer($student, $this->chefProgramme);

        $this->assertSame([$newModule->id], $student->modules()->pluck('modules.id')->all());
    }

    public function test_a_transfer_within_the_same_department_leaves_the_number_alone(): void
    {
        $sameDepartmentProgramme = Programme::factory()->create([
            'department_id' => $this->chefDepartment->id,
            'duration_months' => 36,
        ]);

        $number = $this->numberFor($this->chefDepartment, 5);
        $student = $this->studentInDepartment($this->chefDepartment, $number);

        $this->transfer($student, $sameDepartmentProgramme);

        $this->assertSame($number, $student->refresh()->student_number);
    }

    public function test_an_explicitly_supplied_number_wins_over_reissue(): void
    {
        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2));

        $this->transfer($student, $this->chefProgramme, [
            'student_number' => 'S20260999',
        ]);

        $this->assertSame('S20260999', $student->refresh()->student_number);
    }

    public function test_an_explicit_cohort_wins_over_the_automatic_match(): void
    {
        $academicYear = AcademicYear::factory()->create();

        $oldCohort = Cohort::factory()->create([
            'name' => 'August 2026',
            'department_id' => $this->pastryDepartment->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $chosen = Cohort::factory()->create([
            'name' => 'January 2027',
            'department_id' => $this->chefDepartment->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2), '2026-08-04', $oldCohort);

        $this->transfer($student, $this->chefProgramme, ['cohort_id' => $chosen->id]);

        $this->assertSame($chosen->id, $student->refresh()->profile->cohort_id);
    }

    public function test_it_leaves_the_graduation_date_alone_when_the_programme_length_is_ambiguous(): void
    {
        // A short course such as "3 Months / 6 Months" has no single length.
        $shortCourse = Programme::factory()->create([
            'department_id' => $this->chefDepartment->id,
            'duration_months' => null,
        ]);

        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2), '2026-08-04');
        $student->profile->update(['expected_graduation_date' => null]);

        $this->transfer($student, $shortCourse);

        $this->assertNull(
            $student->refresh()->profile->expected_graduation_date,
            'a guessed duration would place the date years out',
        );
    }

    public function test_it_reports_what_changed(): void
    {
        $student = $this->studentInDepartment($this->pastryDepartment, $this->numberFor($this->pastryDepartment, 2));

        $result = $this->transfer($student, $this->chefProgramme);

        $this->assertSame($this->numberFor($this->pastryDepartment, 2), $result['student_number_from']);
        $this->assertNotSame($result['student_number_from'], $result['student_number_to']);
        $this->assertSame($this->pastryDepartment->id, $result['previous_department_id']);
        $this->assertSame($this->chefDepartment->id, $result['department_id']);
    }

    /**
     * A number that encodes the given department, so the fixtures do not depend
     * on the auto-incremented department ids.
     */
    protected function numberFor(Department $department, int $sequence, int $year = 2026): string
    {
        return 'S'.$year.IdGenerator::departmentSegment($department->id).str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    protected function transfer(User $student, Programme $programme, array $input = []): array
    {
        $student->loadMissing('programme');

        return app(ProgrammeTransferService::class)->transfer($student, $programme, $input, $student->programme);
    }

    /**
     * A student whose programme belongs to the given department.
     */
    protected function studentInDepartment(
        Department $department,
        string $studentNumber,
        ?string $enrollmentDate = null,
        ?Cohort $cohort = null,
    ): User {
        $currentProgramme = Programme::factory()->create([
            'department_id' => $department->id,
            'duration_months' => $department->id === $this->chefDepartment->id ? 36 : 24,
        ]);

        $user = User::factory()->create([
            'programme_id' => $currentProgramme->id,
            'student_number' => $studentNumber,
        ]);

        Profile::factory()->forUser($user)->create([
            'student_number' => $studentNumber,
            'cohort_id' => $cohort?->id,
            'enrollment_date' => $enrollmentDate,
            'status' => 'active',
        ]);

        return $user->fresh();
    }
}
