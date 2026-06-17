<?php

namespace App\Http\Controllers\Hive\Bursar;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Expense::with(['user', 'category', 'vendor', 'budget', 'approver'])
            ->orderByDesc('created_at');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->has('budget_id') && $request->budget_id) {
            $query->where('budget_id', $request->budget_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        return Inertia::render('Bursar/Expense/Index', [
            'expenses' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['status', 'category_id', 'budget_id', 'search', 'date_from', 'date_to']),
            'statuses' => ['pending', 'approved', 'rejected', 'paid', 'cancelled'],
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
            'notes' => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();

        $expense = Expense::create($data);

        return back()->with('success', 'Expense created successfully.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
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
            'notes' => 'nullable|string',
        ]);

        $expense->update($data);

        return back()->with('success', 'Expense updated successfully.');
    }

    public function show(Expense $expense): Response
    {
        $expense->load(['user', 'category', 'vendor', 'budget', 'approver']);

        return Inertia::render('Bursar/Expense/Show', [
            'expense' => $expense,
        ]);
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->status === 'paid') {
            return back()->with('error', 'Cannot delete a paid expense.');
        }

        $expense->delete();

        return redirect()->route('bursar.expenses.index')->with('success', 'Expense deleted.');
    }

    /**
     * Approve an expense.
     */
    public function approve(Request $request, Expense $expense): RedirectResponse
    {
        if ($expense->status !== 'pending') {
            return back()->with('error', 'Only pending expenses can be approved.');
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Expense approved.');
    }

    /**
     * Reject an expense.
     */
    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        $data = $request->validate([
            'notes' => 'required|string',
        ]);

        if ($expense->status !== 'pending') {
            return back()->with('error', 'Only pending expenses can be rejected.');
        }

        $expense->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $data['notes'],
        ]);

        return back()->with('success', 'Expense rejected.');
    }

    /**
     * Mark expense as paid.
     */
    public function markPaid(Request $request, Expense $expense): RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($expense->status !== 'approved') {
            return back()->with('error', 'Only approved expenses can be marked as paid.');
        }

        $expense->update([
            'status' => 'paid',
            'payment_method' => $data['payment_method'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'],
        ]);

        return back()->with('success', 'Expense marked as paid.');
    }

    /**
     * Get expense categories for management.
     */
    public function categories(Request $request): Response
    {
        $categories = ExpenseCategory::orderBy('name')->get();

        return Inertia::render('Bursar/Expense/Categories', [
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