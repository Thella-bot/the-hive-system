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

        $response->assertForbidden();
    }

    public function test_payment_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.payments.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Payment/Index'));
    }

    public function test_payment_store_creates_payment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $invoice = Invoice::factory()->create();

        $response = $this->post(route('hive.finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
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
            'amount' => '',
            'payment_method' => '',
        ]);

        $response->assertSessionHasErrors(['invoice_id', 'amount', 'payment_method']);
    }

    public function test_payment_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $payment = Payment::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.payments.show', $payment));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Payment/Show'));
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

    public function test_payment_destroy_deletes_payment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $payment = Payment::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.finance.payments.destroy', $payment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}