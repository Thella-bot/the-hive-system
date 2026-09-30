<?php

declare(strict_types=1);

namespace Tests\Feature\Hive;

use App\Models\Cohort;
use App\Models\Department;
use App\Models\Profile;
use App\Models\Programme;
use App\Models\User;
use App\Services\IdGenerator;
use App\Services\StudentNumberService;

class StudentControllerTest extends HiveTestCase
{
    public function test_student_index_requires_admin_role(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.students.index'));

        $response->assertRedirect();
    }

    public function test_student_index_returns_success_for_registrar(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $this->actingAs($user);

        $response = $this->get(route('hive.students.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Students/Index'));
    }

    public function test_student_index_paginates_students(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        User::factory()->count(5)->create()->each(fn ($u) => $u->assignRole('student'));

        $this->actingAs($user);

        $response = $this->get(route('hive.students.index'));

        $response->assertOk();
    }

    public function test_student_create_returns_success(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $this->actingAs($user);

        $response = $this->get(route('hive.students.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Students/Create'));
    }

    public function test_student_store_creates_new_student(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $this->actingAs($user);

        Cohort::factory()->create();
        Programme::factory()->create();

        $response = $this->post(route('hive.students.store'), [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'cohort_id' => Cohort::first()->id,
        ]);

        $response->assertRedirect(route('hive.students.index'));
        $this->assertDatabaseHas('users', ['email' => 'student@example.com']);
    }

    public function test_student_store_validates_required_fields(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $this->actingAs($user);

        $response = $this->post(route('hive.students.store'), [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_student_show_returns_success(): void
    {
        $viewer = User::factory()->create(['approved_at' => now()]);
        $viewer->assignRole('registrar');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($viewer);

        $response = $this->get(route('hive.students.show', $student));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Students/Show'));
    }

    public function test_student_show_returns_403_for_unauthorized_user(): void
    {
        $viewer = User::factory()->create(['approved_at' => now()]);
        $viewer->assignRole('student');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($viewer);

        $response = $this->get(route('hive.students.show', $student));

        $response->assertRedirect();
    }

    public function test_student_edit_returns_success(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($user);

        Department::factory()->create();
        Cohort::factory()->create();

        $response = $this->get(route('hive.students.edit', $student));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Students/Edit'));
    }

    public function test_student_update_updates_student(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($user);

        Cohort::factory()->create();

        $response = $this->patch(route('hive.students.update', $student), [
            'name' => 'Updated Student',
            'email' => $student->email,
            'role' => 'student',
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'cohort_id' => Cohort::first()->id,
        ]);

        $response->assertRedirect(route('hive.students.index'));
        $this->assertDatabaseHas('users', ['email' => $student->email, 'name' => 'Updated Student']);
    }

    public function test_student_destroy_deletes_student(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($user);

        $response = $this->delete(route('hive.students.destroy', $student));

        $response->assertRedirect(route('hive.students.index'));
        $this->assertSoftDeleted('users', ['id' => $student->id]);
    }

    public function test_student_destroy_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('student');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($user);

        $response = $this->delete(route('hive.students.destroy', $student));

        $response->assertRedirect();
    }

    public function test_generate_proof_pdf_returns_403_for_unauthorized_user(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('student');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($user);

        $response = $this->getJson(route('hive.students.generate-proof', $student));

        $response->assertStatus(403);
    }

    public function test_generate_certificate_returns_403_for_unauthorized_user(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('admissions-officer');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($user);

        $response = $this->getJson(route('hive.students.generate-certificate', $student));

        $response->assertStatus(403);
    }

    public function test_generate_reference_returns_403_for_unauthorized_user(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('librarian');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        $this->actingAs($user);

        $response = $this->getJson(route('hive.students.generate-reference', $student));

        $response->assertStatus(403);
    }

    public function test_updating_the_programme_reissues_the_student_number(): void
    {
        $this->actingAsRegistrar();

        $pastry = Department::factory()->create(['name' => 'Pastry & Bakery']);
        $chef = Department::factory()->create(['name' => 'Culinary Arts']);

        $from = Programme::factory()->create(['department_id' => $pastry->id]);
        $to = Programme::factory()->create(['department_id' => $chef->id, 'duration_months' => 36]);

        $oldNumber = 'S2026'.IdGenerator::departmentSegment($pastry->id).'02';

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');
        $student->update(['programme_id' => $from->id, 'student_number' => $oldNumber]);

        Profile::factory()->forUser($student)->create([
            'student_number' => $oldNumber,
            'status' => 'active',
        ]);

        $response = $this->patch(route('hive.students.update', $student), [
            'name' => $student->name,
            'email' => $student->email,
            'programme_id' => $to->id,
        ]);

        $response->assertRedirect(route('hive.students.index'));

        $student->refresh();

        $this->assertNotSame($oldNumber, $student->student_number);
        $this->assertSame(
            $chef->id,
            (new StudentNumberService)->departmentIdFor($student->student_number),
        );
        $this->assertSame($student->student_number, $student->profile->student_number);
    }

    public function test_the_administrator_is_told_when_the_number_changes(): void
    {
        $this->actingAsRegistrar();

        $pastry = Department::factory()->create();
        $chef = Department::factory()->create();

        $from = Programme::factory()->create(['department_id' => $pastry->id]);
        $to = Programme::factory()->create(['department_id' => $chef->id, 'duration_months' => 36]);

        $oldNumber = 'S2026'.IdGenerator::departmentSegment($pastry->id).'02';

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');
        $student->update(['programme_id' => $from->id, 'student_number' => $oldNumber]);

        Profile::factory()->forUser($student)->create([
            'student_number' => $oldNumber,
            'status' => 'active',
        ]);

        $response = $this->patch(route('hive.students.update', $student), [
            'name' => $student->name,
            'email' => $student->email,
            'programme_id' => $to->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString(
            $oldNumber,
            session('success'),
            'the old number should be named in the confirmation',
        );
    }

    public function test_updating_the_programme_within_one_department_keeps_the_number(): void
    {
        $this->actingAsRegistrar();

        $department = Department::factory()->create();

        $from = Programme::factory()->create(['department_id' => $department->id]);
        $to = Programme::factory()->create(['department_id' => $department->id, 'duration_months' => 36]);

        $number = 'S2026'.IdGenerator::departmentSegment($department->id).'07';

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');
        $student->update(['programme_id' => $from->id, 'student_number' => $number]);

        Profile::factory()->forUser($student)->create([
            'student_number' => $number,
            'status' => 'active',
        ]);

        $this->patch(route('hive.students.update', $student), [
            'name' => $student->name,
            'email' => $student->email,
            'programme_id' => $to->id,
        ])->assertRedirect(route('hive.students.index'));

        $this->assertSame($number, $student->refresh()->student_number);
    }

    public function test_editing_the_student_number_alone_keeps_both_records_in_step(): void
    {
        $this->actingAsRegistrar();

        $programme = Programme::factory()->create();

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');
        $student->update(['programme_id' => $programme->id, 'student_number' => 'S20260407']);

        Profile::factory()->forUser($student)->create([
            'student_number' => 'S20260407',
            'status' => 'active',
        ]);

        $this->patch(route('hive.students.update', $student), [
            'name' => $student->name,
            'email' => $student->email,
            'programme_id' => $programme->id,
            'student_number' => 'S20260421',
        ])->assertRedirect(route('hive.students.index'));

        $student->refresh();

        $this->assertSame('S20260421', $student->student_number, 'the user row must not keep the old number');
        $this->assertSame('S20260421', $student->profile->student_number);
    }
}
