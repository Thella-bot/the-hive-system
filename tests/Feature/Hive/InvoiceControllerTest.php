<?php

namespace Tests\Feature\Hive;

use App\Models\Invoice;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceControllerTest extends HiveTestCase
{
    public function test_invoice_index_requires_finance_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.invoices.index'));

        $response->assertRedirect();
    }

    public function test_invoice_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.invoices.index'));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Finance/Invoice/Index'));
    }

    public function test_invoice_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.invoices.create'));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Finance/Invoice/Create'));
    }

    public function test_invoice_store_creates_invoice(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        Programme::factory()->create();

        $response = $this->post(route('hive.finance.invoices.store'), [
            'user_id' => User::factory()->create()->id,
            'programme_id' => Programme::first()->id,
            'type' => 'registration',
            'amount' => 500.00,
            'academic_year' => '2025/2026',
            'due_date' => '2026-09-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'type' => 'registration',
            'amount' => 500.00,
        ]);
    }

    public function test_invoice_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->post(route('hive.finance.invoices.store'), [
            'user_id' => '',
            'programme_id' => '',
            'type' => '',
            'amount' => '',
        ]);

        $response->assertSessionHasErrors(['user_id', 'programme_id', 'type', 'amount']);
    }

    public function test_invoice_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $invoice = Invoice::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.invoices.show', $invoice));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Finance/Invoice/Show'));
    }

    public function test_invoice_update_modifies_invoice(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $invoice = Invoice::factory()->create();

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.invoices.update', $invoice), [
            'status' => 'paid',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }

    public function test_invoice_destroy_deletes_invoice(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $invoice = Invoice::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.finance.invoices.destroy', $invoice));

        $response->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_student_can_view_own_invoice(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $invoice = Invoice::factory()->create([
            'user_id' => $student->id,
        ]);

        $this->actingAs($student);

        $response = $this->get(route('hive.finance.invoices.show', $invoice));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Finance/Invoice/Show'));
    }

    public function test_student_cannot_view_other_invoice(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $otherStudent = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $otherStudent->id,
        ]);

        $this->actingAs($student);

        $response = $this->get(route('hive.finance.invoices.show', $invoice));

        $response->assertRedirect();
    }
}
