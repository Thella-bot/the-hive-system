<?php

namespace App\Http\Controllers\Hive\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFilters;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    use HasFilters;

    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * Check if user can access the given expense.
     * Admins can access all; department staff only expenses from their department's budgets.
     */
    private function authorizeExpense(Expense $expense): void
    {
        $user = auth()->user();

        // Admins can access all expenses
        if ($user->isAdmin()) {
            return;
        }

        // Finance/HR can only access expenses from their department's budgets
        $userDeptId = $user->profile?->department_id;
        $expenseDeptId = $expense->budget?->department_id;

        if (!$userDeptId || $expenseDeptId !== $userDeptId) {
            abort(403, 'You can only access expenses from your department.');
        }
    }

    public function index(Request $request): Response
    {
        $query = Expense::with(['user', 'category', 'vendor', 'budget', 'approver'])
            ->orderByDesc('created_at');

        $this->applyFilters($query, $request, [
            'status' => true,
            'search' => true,
            'searchColumns' => ['expense_number', 'description', 'reference_number'],
            'date_from' => true,
            'date_to' => true,
            'dateColumn' => 'expense_date',
            'category_id' => true,
            'categoryColumn' => 'expense_category_id',
            'budget_id' => true,
        ]);

        return Inertia::render('Hive/Finance/Expense/Index', [
            'expenses' => $query->paginate(20)->withQueryString(),
            'filters' => $this->getFilterInputs($request, ['status', 'category_id', 'budget_id', 'search', 'date_from', 'date_to']),
            'statuses' => ['pending', 'approved', 'rejected', 'paid', 'cancelled'],
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
            'budgets' => Budget::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Hive/Finance/Expense/Create', [
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
            'budgets' => Budget::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'receipt_path' => 'nullable|string',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data['user_id'] = auth()->id();

        // Sanitize text fields
        $data['description'] = strip_tags($data['description']);
        $data['notes'] = isset($data['notes']) ? strip_tags($data['notes']) : null;
        $data['reference_number'] = isset($data['reference_number']) ? strip_tags($data['reference_number']) : null;

        $expense = Expense::create($data);

        $this->audit->logCreated($expense);

        return back()->with('success', 'Expense created successfully.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($expense);

        $data = $request->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'budget_id' => 'nullable|exists:budgets,id',
            'description' => 'sometimes|string|max:500',
            'amount' => 'sometimes|numeric|min:0.01',
            'expense_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'status' => 'sometimes|in:pending,approved,rejected,paid,cancelled',
            'receipt_path' => 'nullable|string',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Sanitize text fields
        $data['description'] = isset($data['description']) ? strip_tags($data['description']) : null;
        $data['notes'] = isset($data['notes']) ? strip_tags($data['notes']) : null;
        $data['reference_number'] = isset($data['reference_number']) ? strip_tags($data['reference_number']) : null;

        $oldValues = $expense->getOriginal();
        $expense->update($data);

        $this->audit->logUpdated($expense, $oldValues);

        return back()->with('success', 'Expense updated successfully.');
    }

    public function show(Expense $expense): Response
    {
        $this->authorizeExpense($expense);

        $expense->load(['user', 'category', 'vendor', 'budget', 'approver']);

        return Inertia::render('Hive/Finance/Expense/Show', [
            'expense' => $expense,
        ]);
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($expense);

        if ($expense->is_paid) {
            return back()->with('error', 'Cannot delete a paid expense.');
        }

        $this->audit->logDeleted($expense);

        $expense->delete();

        return redirect()->route('hive.finance.expenses.index')->with('success', 'Expense deleted.');
    }

    /**
     * Approve an expense.
     */
    public function approve(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($expense);

        if (!$expense->is_pending) {
            return back()->with('error', 'Only pending expenses can be approved.');
        }

        $oldValues = $expense->getOriginal();

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->audit->logUpdated($expense, $oldValues);

        return back()->with('success', 'Expense approved.');
    }

    /**
     * Reject an expense.
     */
    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($expense);

        $data = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        if (!$expense->is_pending) {
            return back()->with('error', 'Only pending expenses can be rejected.');
        }

        $oldValues = $expense->getOriginal();

        $expense->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => strip_tags($data['notes']),
        ]);

        $this->audit->logUpdated($expense, $oldValues);

        return back()->with('success', 'Expense rejected.');
    }

    /**
     * Mark expense as paid.
     */
    public function markPaid(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpense($expense);

        $data = $request->validate([
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (!$expense->is_approved) {
            return back()->with('error', 'Only approved expenses can be marked as paid.');
        }

        // Sanitize text fields
        $data['reference_number'] = isset($data['reference_number']) ? strip_tags($data['reference_number']) : null;
        $data['notes'] = isset($data['notes']) ? strip_tags($data['notes']) : null;

        $oldValues = $expense->getOriginal();

        $expense->update([
            'status' => 'paid',
            'payment_method' => $data['payment_method'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->audit->logUpdated($expense, $oldValues);

        return back()->with('success', 'Expense marked as paid.');
    }

    /**
     * Get expense categories for management.
     */
    public function categories(Request $request): Response
    {
        $categories = ExpenseCategory::orderBy('name')->get();

        return Inertia::render('Hive/Finance/Expense/Categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Create a new expense category.
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'code' => 'required|string|max:20|unique:expense_categories,code',
        ]);

        ExpenseCategory::create($data);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Update an expense category.
     */
    public function updateCategory(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:255',
            'code' => 'sometimes|string|max:20|unique:expense_categories,code,' . $category->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Delete an expense category.
     */
    public function destroyCategory(ExpenseCategory $category): RedirectResponse
    {
        if ($category->expenses()->exists()) {
            return back()->with('error', 'Cannot delete category with associated expenses.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}