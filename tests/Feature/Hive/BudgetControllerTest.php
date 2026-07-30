<?php

namespace Tests\Feature\Hive;

use App\Models\Budget;
use App\Models\Department;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BudgetControllerTest extends HiveTestCase
{
    public function test_budget_index_requires_finance_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.budgets.index'));

        $response->assertForbidden();
    }

    public function test_budget_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.budgets.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Budget/Index'));
    }

    public function test_budget_store_creates_budget(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        Department::factory()->create();
        ExpenseCategory::factory()->create();

        $response = $this->post(route('hive.finance.budgets.store'), [
            'name' => 'Test Budget',
            'academic_year' => '2025/2026',
            'semester' => 1,
            'department_id' => Department::first()->id,
            'expense_category_id' => ExpenseCategory::first()->id,
            'allocated_amount' => 10000.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('budgets', [
            'name' => 'Test Budget',
            'allocated_amount' => 10000.00,
        ]);
    }

    public function test_budget_show_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $budget = Budget::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.budgets.show', $budget));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Budget/Show'));
    }

    public function test_budget_update_modifies_budget(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $budget = Budget::factory()->create();

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.budgets.update', $budget), [
            'allocated_amount' => 15000.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'allocated_amount' => 15000.00,
        ]);
    }

    public function test_budget_activate_changes_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $budget = Budget::factory()->create([
            'status' => 'draft',
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.budgets.activate', $budget));

        $response->assertRedirect();
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'status' => 'active',
        ]);
    }

    public function test_budget_close_changes_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $budget = Budget::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.budgets.close', $budget));

        $response->assertRedirect();
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'status' => 'closed',
        ]);
    }

    public function test_budget_destroy_deletes_budget(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $budget = Budget::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.finance.budgets.destroy', $budget));

        $response->assertRedirect();
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }
}