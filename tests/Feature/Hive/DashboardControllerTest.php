<?php

namespace Tests\Feature\Hive;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;

class DashboardControllerTest extends HiveTestCase
{
    public function test_dashboard_redirects_to_login_for_guests(): void
    {
        $response = $this->get(route('hive.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_returns_success_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $response = $this->get(route('hive.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Dashboard'));
    }

    public function test_dashboard_returns_success_for_student(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Dashboard'));
    }

    public function test_student_dashboard_reports_correct_invoice_totals(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        Invoice::factory()->create(['user_id' => $user->id, 'amount' => 1000.00]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'amount' => 500.00]);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => 400.00,
            'status' => 'completed',
        ]);
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => 50.00,
            'status' => 'failed',
        ]);

        $response = $this->get(route('hive.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Dashboard'));

        $props = $response->viewData('page')['props'];

        // totalFees = 1000 + 500; totalPaid = 400 (only completed); balance = 600
        $this->assertEquals(1500.0, (float) $props['totalFees']);
        $this->assertEquals(400.0, (float) $props['totalPaid']);
        $this->assertEquals(1100.0, (float) $props['remainingBalance']);
    }

    public function test_dashboard_returns_success_for_chef_instructor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chef-instructor');

        $this->actingAs($user);

        $response = $this->get(route('hive.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Dashboard'));
    }
}