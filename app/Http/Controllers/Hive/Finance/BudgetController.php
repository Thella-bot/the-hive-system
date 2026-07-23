<?php

namespace App\Http\Controllers\Hive\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFilters;
use App\Models\Budget;
use App\Models\Department;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    use HasFilters;

    /**
     * Check if user can access the given budget.
     * Admins can access all; department-heads only their department's budgets.
     */
    private function authorizeBudget(Budget $budget): void
    {
        $user = auth()->user();

        // Admins can access all budgets
        if ($user->isAdmin()) {
            return;
        }

        // Finance/HR can only access budgets in their department
        $userDeptId = $user->profile?->department_id;
        if (!$userDeptId || $budget->department_id !== $userDeptId) {
            abort(403, 'You can only access budgets from your department.');
        }
    }

    public function index(Request $request): Response
    {
        $query = Budget::with(['category', 'department'])
            ->orderByDesc('created_at');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('academic_year') && $request->academic_year) {
            $query->where('academic_year', $request->academic_year);
        }

        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $budgets = $query->paginate(20)->withQueryString();

        // Add computed attributes
        $budgets->getCollection()->transform(function ($budget) {
            $budget->spent_amount = $budget->spent_amount;
            $budget->available_amount = $budget->available_amount;
            $budget->percent_used = $budget->percent_used;
            $budget->is_overspent = $budget->is_overspent;
            return $budget;
        });

        return Inertia::render('Hive/Finance/Budget/Index', [
            'budgets' => $budgets,
            'filters' => $this->getFilterInputs($request, ['status', 'academic_year', 'department_id']),
            'statuses' => ['draft', 'active', 'closed'],
            'departments' => Department::orderBy('name')->get(),
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
            'academicYears' => Budget::distinct()->pluck('academic_year')->filter()->sort()->reverse()->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'academic_year' => 'required|string',
            'semester' => 'nullable|integer|min:1|max:3',
            'department_id' => 'nullable|exists:departments,id',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'approved_budget' => 'required|numeric|min:0',
            'allocated_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (!isset($data['allocated_amount'])) {
            $data['allocated_amount'] = $data['approved_budget'];
        }

        Budget::create($data);

        return back()->with('success', 'Budget created successfully.');
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorizeBudget($budget);

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'academic_year' => 'sometimes|string',
            'semester' => 'nullable|integer|min:1|max:3',
            'department_id' => 'nullable|exists:departments,id',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'approved_budget' => 'sometimes|numeric|min:0',
            'allocated_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'sometimes|in:draft,active,closed',
            'notes' => 'nullable|string|max:2000',
        ]);

        $budget->update($data);

        return back()->with('success', 'Budget updated successfully.');
    }

    public function show(Budget $budget): Response
    {
        $this->authorizeBudget($budget);

        $budget->load(['category', 'department', 'expenses.user', 'expenses.approver']);

        // Add computed attributes
        $budget->spent_amount = $budget->spent_amount;
        $budget->available_amount = $budget->available_amount;
        $budget->percent_used = $budget->percent_used;
        $budget->is_overspent = $budget->is_overspent;

        return Inertia::render('Hive/Finance/Budget/Show', [
            'budget' => $budget,
        ]);
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorizeBudget($budget);

        if ($budget->expenses()->exists()) {
            return back()->with('error', 'Cannot delete budget with associated expenses.');
        }

        $budget->delete();

        return redirect()->route('finance.budgets.index')->with('success', 'Budget deleted.');
    }

    /**
     * Activate a budget.
     */
    public function activate(Budget $budget): RedirectResponse
    {
        $this->authorizeBudget($budget);

        if ($budget->status !== 'draft') {
            return back()->with('error', 'Only draft budgets can be activated.');
        }

        // Close any other active budgets for the same academic year/category
        Budget::where('academic_year', $budget->academic_year)
            ->where('expense_category_id', $budget->expense_category_id)
            ->where('status', 'active')
            ->update(['status' => 'closed']);

        $budget->update(['status' => 'active']);

        return back()->with('success', 'Budget activated.');
    }

    /**
     * Close a budget.
     */
    public function close(Budget $budget): RedirectResponse
    {
        $this->authorizeBudget($budget);

        if ($budget->status !== 'active') {
            return back()->with('error', 'Only active budgets can be closed.');
        }

        $budget->update(['status' => 'closed']);

        return back()->with('success', 'Budget closed.');
    }

    /**
     * Get budget summary for dashboard.
     */
    public function summary(Request $request): Response
    {
        $academicYear = $request->get('academic_year', date('Y'));

        $budgets = Budget::where('academic_year', $academicYear)
            ->where('status', 'active')
            ->with('category')
            ->get();

        $budgets->getCollection()->transform(function ($budget) {
            $budget->spent_amount = $budget->spent_amount;
            $budget->available_amount = $budget->available_amount;
            $budget->percent_used = $budget->percent_used;
            $budget->is_overspent = $budget->is_overspent;
            return $budget;
        });

        $totalApproved = $budgets->sum('approved_budget');
        $totalAllocated = $budgets->sum('allocated_amount');
        $totalSpent = $budgets->sum('spent_amount');
        $totalAvailable = $budgets->sum('available_amount');

        return Inertia::render('Hive/Bursar/Budget/Summary', [
            'budgets' => $budgets,
            'summary' => [
                'total_approved' => $totalApproved,
                'total_allocated' => $totalAllocated,
                'total_spent' => $totalSpent,
                'total_available' => $totalAvailable,
            ],
            'academicYear' => $academicYear,
        ]);
    }
}