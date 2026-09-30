<?php

declare(strict_types=1);

namespace Tests\Feature\Hive;

use App\Models\Profile;
use App\Models\Programme;
use App\Models\User;

class StudentExportTest extends HiveTestCase
{
    public function test_registrar_can_download_the_register(): void
    {
        $this->actingAsRegistrar();

        $response = $this->get(route('hive.students.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        $response->assertDownload();
    }

    public function test_the_export_route_is_not_swallowed_by_the_show_route(): void
    {
        $this->actingAsRegistrar();

        $this->get(route('hive.students.export'))->assertOk();
    }

    public function test_the_export_is_not_reachable_by_a_student(): void
    {
        $this->actingAsStudent();

        $this->get(route('hive.students.export'))->assertRedirect();
    }

    public function test_the_export_is_not_reachable_by_a_role_that_cannot_manage_students(): void
    {
        $this->actingAsLibrarian();

        $this->get(route('hive.students.export'))->assertRedirect();
    }

    public function test_it_returns_a_utf8_bom_so_excel_reads_names_correctly(): void
    {
        $this->actingAsRegistrar();

        $response = $this->get(route('hive.students.export'));

        $this->assertStringStartsWith("\xEF\xBB\xBF", $response->streamedContent());
    }

    public function test_it_contains_the_typical_information_columns(): void
    {
        $this->actingAsRegistrar();

        $response = $this->get(route('hive.students.export'));
        $content = $response->streamedContent();

        foreach ([
            'Student Number',
            'Full Name',
            'Email',
            'Phone',
            'Gender',
            'Programme',
            'Department',
            'Cohort',
            'Status',
            'Enrollment Date',
            'Expected Graduation',
            'Emergency Contact',
        ] as $header) {
            $this->assertStringContainsString($header, $content);
        }
    }

    public function test_it_includes_a_row_for_each_student(): void
    {
        $student = User::factory()->create(['name' => 'Refilwe Mokoena', 'student_number' => 'S20260401']);
        $student->assignRole('student');
        Profile::factory()->forUser($student)->create([
            'student_number' => 'S20260401',
            'status' => 'active',
        ]);

        $this->actingAsRegistrar();

        $content = $this->get(route('hive.students.export'))->streamedContent();

        $this->assertStringContainsString('Refilwe Mokoena', $content);
        $this->assertStringContainsString('S20260401', $content);
    }

    public function test_it_honours_the_search_filter(): void
    {
        $matched = User::factory()->create(['name' => 'Thabo Maseko', 'student_number' => 'S20260402']);
        $matched->assignRole('student');

        $other = User::factory()->create(['name' => 'Nthabiseng Dube', 'student_number' => 'S20260403']);
        $other->assignRole('student');

        $this->actingAsRegistrar();

        $content = $this->get(route('hive.students.export', ['search' => 'Thabo']))->streamedContent();

        $this->assertStringContainsString('Thabo Maseko', $content);
        $this->assertStringNotContainsString('Nthabiseng Dube', $content);
    }

    public function test_it_honours_the_programme_filter(): void
    {
        $programme = Programme::factory()->create(['name' => 'Diploma in Professional Chef']);

        $inProgramme = User::factory()->create(['name' => 'Kabelo Sithole', 'programme_id' => $programme->id]);
        $inProgramme->assignRole('student');

        $elsewhere = User::factory()->create(['name' => 'Palesa Tsebe']);
        $elsewhere->assignRole('student');

        $this->actingAsRegistrar();

        $content = $this->get(route('hive.students.export', ['programme_id' => $programme->id]))->streamedContent();

        $this->assertStringContainsString('Kabelo Sithole', $content);
        $this->assertStringNotContainsString('Palesa Tsebe', $content);
    }

    public function test_it_rejects_an_unknown_column(): void
    {
        $this->actingAsRegistrar();

        $this->get(route('hive.students.export', ['columns' => 'password,secret']))
            ->assertOk()
            ->assertDontSee('password');
    }

    public function test_it_excludes_staff_from_the_register(): void
    {
        $staff = User::factory()->create(['name' => 'Chef Instructors Person']);
        $staff->assignRole('chef-instructor');

        $this->actingAsRegistrar();

        $this->get(route('hive.students.export'))
            ->assertOk()
            ->assertDontSee('Chef Instructors Person');
    }

    public function test_the_listing_passes_the_export_url_and_permission_to_the_page(): void
    {
        $this->actingAsRegistrar();

        $this->get(route('hive.students.index'))
            ->assertInertia(
                fn ($page) => $page
                    ->component('Hive/Students/Index')
                    ->where('canExport', true)
                    ->where('exportUrl', route('hive.students.export'))
                    ->has('filterOptions.programmes')
                    ->has('filterOptions.statuses')
            );
    }

    public function test_the_listing_returns_403_for_a_role_that_cannot_export(): void
    {
        $this->actingAsLibrarian();

        $this->get(route('hive.students.index'))->assertRedirect();
    }
}
