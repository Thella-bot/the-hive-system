<?php

namespace Tests\Feature\Hive;

use App\Models\Cohort;
use App\Models\Department;
use App\Models\Programme;
use App\Models\User;

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

    public function test_generate_proof_pdf_returns_pdf_for_registrar(): void
    {
        $user = User::factory()->create(['approved_at' => now()]);
        $user->assignRole('registrar');

        $student = User::factory()->create(['approved_at' => now()]);
        $student->assignRole('student');

        \App\Models\Programme::factory()->create();

        \App\Models\Enrollment::factory()->create([
            'user_id' => $student->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('hive.students.generate-proof', $student));

        $response->assertOk();
        $this->assertStringContainsString('pdf', $response->headers->get('content-type'));
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
}