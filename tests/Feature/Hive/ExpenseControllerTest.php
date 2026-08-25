<?php

namespace Tests\Feature\Hive;

use App\Models\Budget;
use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Profile;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseControllerTest extends HiveTestCase
{
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->department = Department::factory()->create();
    }

    private function financeUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('finance');
        Profile::factory()->for($user)->create([
            'department_id' => $this->department->id,
        ]);

        return $user;
    }

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
        $user = $this->financeUser();

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.expenses.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Expense/Index'));
    }

    public function test_expense_create_returns_success(): void
    {
        $user = $this->financeUser();

        $this->actingAs($user);

        $response = $this->get(route('hive.finance.expenses.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Hive/Finance/Expense/Create'));
    }

    public function test_expense_store_creates_expense(): void
    {
        $user = $this->financeUser();
        ExpenseCategory::factory()->create();
        Supplier::factory()->create();
        $budget = Budget::factory()->create([
            'department_id' => $this->department->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hive.finance.expenses.store'), [
            'expense_category_id' => ExpenseCategory::first()->id,
            'vendor_id' => Supplier::first()->id,
            'budget_id' => $budget->id,
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
        $user = $this->financeUser();
        ExpenseCategory::factory()->create();
        Supplier::factory()->create();
        $budget = Budget::factory()->create([
            'department_id' => $this->department->id,
        ]);

        $expense = Expense::factory()->create([
            'status' => 'pending',
            'expense_category_id' => ExpenseCategory::first()->id,
            'vendor_id' => Supplier::first()->id,
            'budget_id' => $budget->id,
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
        $user = $this->financeUser();
        ExpenseCategory::factory()->create();
        Supplier::factory()->create();
        $budget = Budget::factory()->create([
            'department_id' => $this->department->id,
        ]);

        $expense = Expense::factory()->create([
            'status' => 'pending',
            'expense_category_id' => ExpenseCategory::first()->id,
            'vendor_id' => Supplier::first()->id,
            'budget_id' => $budget->id,
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('hive.finance.expenses.reject', $expense), [
            'notes' => 'Not approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'rejected',
        ]);
    }

    public function test_expense_mark_paid_changes_status(): void
    {
        $user = $this->financeUser();
        ExpenseCategory::factory()->create();
        Supplier::factory()->create();
        $budget = Budget::factory()->create([
            'department_id' => $this->department->id,
        ]);

        $expense = Expense::factory()->create([
            'status' => 'approved',
            'expense_category_id' => ExpenseCategory::first()->id,
            'vendor_id' => Supplier::first()->id,
            'budget_id' => $budget->id,
        ]);

        $this->actingAs($user);
        $this->withoutExceptionHandling();

        $response = $this->patch(route('hive.finance.expenses.markPaid', $expense), [
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'paid',
        ]);
    }

    public function test_expense_destroy_deletes_expense(): void
    {
        $user = $this->financeUser();
        ExpenseCategory::factory()->create();
        Supplier::factory()->create();
        $budget = Budget::factory()->create([
            'department_id' => $this->department->id,
        ]);

        $expense = Expense::factory()->create([
            'status' => 'pending',
            'expense_category_id' => ExpenseCategory::first()->id,
            'vendor_id' => Supplier::first()->id,
            'budget_id' => $budget->id,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('hive.finance.expenses.destroy', $expense));

        $response->assertRedirect();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}