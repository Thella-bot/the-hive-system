<?php

namespace Tests\Feature\Hive;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentControllerTest extends HiveTestCase
{
    public function test_payment_index_requires_finance_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.payments.index'));

        $response->assertRedirect();
    }

    public function test_payment_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.payments.index'));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Finance/Payment/Index'));
    }

    public function test_payment_store_creates_payment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $invoice = Invoice::factory()->create();

        $response = $this->post(route('hive.finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'items' => [
                ['description' => 'Tuition Fee', 'qty' => 1, 'unit_price' => 100.00, 'total' => 100.00],
            ],
            'payment_method' => 'bank_transfer',
            'payment_date' => '2026-07-27',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'payment_method' => 'bank_transfer',
        ]);
    }

    public function test_payment_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->post(route('hive.finance.payments.store'), [
            'invoice_id' => '',
            'items' => [],
            'payment_method' => '',
        ]);

        $response->assertSessionHasErrors(['invoice_id', 'items', 'payment_method']);
    }

    public function test_payment_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $payment = Payment::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.payments.show', $payment));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Hive/Finance/Payment/Show'));
    }

    public function test_payment_verify_marks_as_completed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $payment = Payment::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.payments.verify', $payment));

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);
    }

    public function test_payment_receipt_download_returns_pdf(): void
    {
        $financeUser = User::factory()->create();
        $financeUser->assignRole('finance');

        $student = User::factory()->create();
        $student->assignRole('student');

        $invoice = Invoice::create([
            'user_id' => $student->id,
            'programme_id' => null,
            'amount' => 1500.00,
            'description' => 'Tuition fees',
            'academic_year' => '2026/2027',
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $student->id,
            'amount' => 1500.00,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->toDateString(),
            'status' => 'completed',
            'notes' => 'Bank transfer reference 12345',
        ]);

        $this->actingAs($financeUser);

        $response = $this->get(route('hive.finance.payments.receipt', $payment));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_payment_destroy_deletes_payment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $payment = Payment::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.finance.payments.destroy', $payment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
