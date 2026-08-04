<?php

namespace Tests\Feature\Hive;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseControllerTest extends HiveTestCase
{
    public function test_expense_index_requires_finance_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.expenses.index'));

        $response->assertRedirect();
    }

    public function test_expense_index_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.expenses.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Expense/Index'));
    }

    public function test_expense_create_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.expenses.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Expense/Create'));
    }

    public function test_expense_store_creates_expense(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $this->actingAs($user);

        Supplier::factory()->create();
        ExpenseCategory::factory()->create();
        Budget::factory()->create();

        $response = $this->post(route('hive.finance.expenses.store'), [
            'expense_category_id' => ExpenseCategory::first()->id,
            'vendor_id' => Supplier::first()->id,
            'budget_id' => Budget::first()->id,
            'description' => 'Test expense',
            'amount' => 150.00,
            'expense_date' => '2026-07-27',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'description' => 'Test expense',
            'amount' => 150.00,
        ]);
    }

    public function test_expense_approve_changes_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $expense = Expense::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.expenses.approve', $expense));

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'approved',
        ]);
    }

    public function test_expense_reject_changes_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $expense = Expense::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.expenses.reject', $expense));

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'rejected',
        ]);
    }

    public function test_expense_mark_paid_changes_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $expense = Expense::factory()->create([
            'status' => 'approved',
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.expenses.markPaid', $expense));

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'paid',
        ]);
    }

    public function test_expense_destroy_deletes_expense(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance');

        $expense = Expense::factory()->create();

        $this->actingAs($user);

        $response = $this->delete(route('hive.finance.expenses.destroy', $expense));

        $response->assertRedirect();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}