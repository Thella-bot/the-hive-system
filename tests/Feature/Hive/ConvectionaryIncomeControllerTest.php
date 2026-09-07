<?php

namespace Tests\Feature\Hive;

use App\Models\ConvectionaryIncome;
use App\Models\User;

class ConvectionaryIncomeControllerTest extends HiveTestCase
{
    public function test_index_requires_finance_role(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        $this->actingAs($student);

        $response = $this->get(route('hive.finance.convectionary.index'));
        $response->assertRedirect();
    }

    public function test_index_returns_success_for_finance(): void
    {
        $this->actingAsFinance();

        $response = $this->get(route('hive.finance.convectionary.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Convectionary/Index'));
    }

    public function test_create_returns_success_for_finance(): void
    {
        $this->actingAsFinance();

        $response = $this->get(route('hive.finance.convectionary.create'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Convectionary/Create'));
    }

    public function test_store_creates_income_record(): void
    {
        $user = $this->actingAsFinance();

        $response = $this->post(route('hive.finance.convectionary.store'), [
            'source' => array_key_first(ConvectionaryIncome::SOURCES),
            'amount' => 1500.50,
            'income_date' => now()->format('Y-m-d'),
            'description' => 'Lunch service',
            'payment_method' => array_key_first(ConvectionaryIncome::METHODS),
            'status' => 'received',
        ]);

        $response->assertRedirect(route('hive.finance.convectionary.index'));
        $this->assertDatabaseHas('convectionary_incomes', [
            'recorded_by' => $user->id,
            'amount' => 1500.50,
        ]);
    }

    public function test_store_denies_unauthorized_user(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        $this->actingAs($student);

        $response = $this->post(route('hive.finance.convectionary.store'), [
            'source' => array_key_first(ConvectionaryIncome::SOURCES),
            'amount' => 100,
            'income_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
    }

    public function test_show_returns_success_for_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $income = ConvectionaryIncome::factory()->create([
            'recorded_by' => $admin->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('hive.finance.convectionary.show', $income->id));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Convectionary/Show'));
    }

    public function test_destroy_removes_record_for_owner(): void
    {
        $user = $this->actingAsFinance();
        $income = ConvectionaryIncome::factory()->create([
            'recorded_by' => $user->id,
        ]);

        $response = $this->delete(route('hive.finance.convectionary.destroy', $income->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('convectionary_incomes', ['id' => $income->id]);
    }
}