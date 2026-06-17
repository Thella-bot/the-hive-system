<?php

namespace App\Http\Controllers\Hive\Bursar;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialReportController extends Controller
{
    /**
     * Financial dashboard overview.
     */
    public function dashboard(Request $request): Response
    {
        $academicYear = $request->get('academic_year', date('Y'));

        // Income (Payments)
        $totalIncome = Payment::where('status', 'completed')
            ->whereYear('payment_date', $academicYear)
            ->sum('amount');

        $monthlyIncome = Payment::where('status', 'completed')
            ->whereYear('payment_date', $academicYear)
            ->selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Expenses
        $totalExpenses = Expense::whereIn('status', ['approved', 'paid'])
            ->whereYear('expense_date', $academicYear)
            ->sum('amount');

        $monthlyExpenses = Expense::whereIn('status', ['approved', 'paid'])
            ->whereYear('expense_date', $academicYear)
            ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Invoices
        $totalInvoiced = Invoice::where('academic_year', $academicYear)
            ->sum('amount');

        $totalCollected = Invoice::where('academic_year', $academicYear)
            ->where('status', 'paid')
            ->sum('amount');

        $pendingInvoices = Invoice::where('academic_year', $academicYear)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->count();

        $overdueInvoices = Invoice::where('academic_year', $academicYear)
            ->where('status', 'overdue')
            ->count();

        // Budget Overview
        $activeBudgets = Budget::where('academic_year', $academicYear)
            ->where('status', 'active')
            ->get();

        $totalBudgetAllocated = $activeBudgets->sum('allocated_amount');
        $totalBudgetSpent = $activeBudgets->sum(function ($budget) {
            return $budget->expenses()
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');
        });

        // Expense by Category
        $expensesByCategory = Expense::whereIn('status', ['approved', 'paid'])
            ->whereYear('expense_date', $academicYear)
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category?->name ?? 'Uncategorized',
                    'total' => $item->total,
                ];
            });

        // Invoice by Type
        $invoicesByType = Invoice::where('academic_year', $academicYear)
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'total' => $item->total,
                    'count' => $item->count,
                ];
            });

        // Recent Transactions
        $recentPayments = Payment::with(['user', 'invoice'])
            ->where('status', 'completed')
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        $recentExpenses = Expense::with(['user', 'category'])
            ->whereIn('status', ['approved', 'paid'])
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        // Key Metrics
        $netPosition = $totalIncome - $totalExpenses;
        $collectionRate = $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 2) : 0;
        $budgetUtilization = $totalBudgetAllocated > 0 ? round(($totalBudgetSpent / $totalBudgetAllocated) * 100, 2) : 0;

        return Inertia::render('Bursar/Reports/Dashboard', [
            'academicYear' => $academicYear,
            'income' => [
                'total' => $totalIncome,
                'monthly' => $monthlyIncome,
            ],
            'expenses' => [
                'total' => $totalExpenses,
                'monthly' => $monthlyExpenses,
            ],
            'invoices' => [
                'total_invoiced' => $totalInvoiced,
                'total_collected' => $totalCollected,
                'pending_count' => $pendingInvoices,
                'overdue_count' => $overdueInvoices,
                'by_type' => $invoicesByType,
            ],
            'budget' => [
                'total_allocated' => $totalBudgetAllocated,
                'total_spent' => $totalBudgetSpent,
                'utilization' => $budgetUtilization,
            ],
            'metrics' => [
                'net_position' => $netPosition,
                'collection_rate' => $collectionRate,
            ],
            'expensesByCategory' => $expensesByCategory,
            'recentPayments' => $recentPayments,
            'recentExpenses' => $recentExpenses,
        ]);
    }

    /**
     * Income report (payments received).
     */
    public function income(Request $request): Response
    {
        $query = Payment::with(['user', 'invoice', 'recorder'])
            ->where('status', 'completed')
            ->orderByDesc('payment_date');

        if ($request->has('academic_year') && $request->academic_year) {
            $query->whereYear('payment_date', $request->academic_year);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->paginate(20)->withQueryString();

        $totalAmount = Payment::where('status', 'completed')
            ->when($request->academic_year, fn($q) => $q->whereYear('payment_date', $request->academic_year))
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
            ->sum('amount');

        return Inertia::render('Bursar/Reports/Income', [
            'payments' => $payments,
            'filters' => $request->only(['academic_year', 'date_from', 'date_to', 'payment_method']),
            'totalAmount' => $totalAmount,
            'methods' => ['cash', 'bank_transfer', 'mobile_money', 'card', 'other'],
        ]);
    }

    /**
     * Expense report.
     */
    public function expenses(Request $request): Response
    {
        $query = Expense::with(['user', 'category', 'vendor', 'budget'])
            ->orderByDesc('expense_date');

        if ($request->has('academic_year') && $request->academic_year) {
            $query->whereYear('expense_date', $request->academic_year);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->paginate(20)->withQueryString();

        $totalAmount = Expense::whereIn('status', ['approved', 'paid'])
            ->when($request->academic_year, fn($q) => $q->whereYear('expense_date', $request->academic_year))
            ->when($request->date_from, fn($q) => $q->whereDate('expense_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('expense_date', '<=', $request->date_to))
            ->sum('amount');

        return Inertia::render('Bursar/Reports/Expenses', [
            'expenses' => $expenses,
            'filters' => $request->only(['academic_year', 'status', 'category_id', 'date_from', 'date_to']),
            'totalAmount' => $totalAmount,
            'statuses' => ['pending', 'approved', 'rejected', 'paid', 'cancelled'],
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Student ledger (all invoices and payments for a student).
     */
    public function studentLedger(Request $request, $userId): Response
    {
        $user = \App\Models\User::findOrFail($userId);

        $invoices = Invoice::where('user_id', $userId)
            ->with('programme')
            ->orderByDesc('created_at')
            ->get();

        $payments = Payment::where('user_id', $userId)
            ->with('invoice')
            ->orderByDesc('payment_date')
            ->get();

        $totalInvoiced = $invoices->sum('amount');
        $totalPaid = $payments->where('status', 'completed')->sum('amount');
        $balance = $totalInvoiced - $totalPaid;

        return Inertia::render('Bursar/Reports/StudentLedger', [
            'user' => $user,
            'invoices' => $invoices,
            'payments' => $payments,
            'summary' => [
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'balance' => $balance,
            ],
        ]);
    }

    /**
     * Age analysis (overdue invoices).
     */
    public function ageAnalysis(Request $request): Response
    {
        $academicYear = $request->get('academic_year', date('Y'));

        $invoices = Invoice::where('academic_year', $academicYear)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->with(['user', 'programme'])
            ->orderBy('due_date')
            ->get();

        $invoices->transform(function ($invoice) {
            $daysOverdue = $invoice->due_date
                ? now()->diffInDays($invoice->due_date, false)
                : 0;

            $invoice->days_overdue = $daysOverdue > 0 ? $daysOverdue : 0;
            $invoice->age_bracket = match (true) {
                $daysOverdue <= 0 => 'current',
                $daysOverdue <= 30 => '1-30 days',
                $daysOverdue <= 60 => '31-60 days',
                $daysOverdue <= 90 => '61-90 days',
                default => '90+ days',
            };

            return $invoice;
        });

        $byAgeBracket = $invoices->groupBy('age_bracket')
            ->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('amount'),
            ]);

        $totalOverdue = $invoices->sum('amount');

        return Inertia::render('Bursar/Reports/AgeAnalysis', [
            'invoices' => $invoices,
            'byAgeBracket' => $byAgeBracket,
            'totalOverdue' => $totalOverdue,
            'academicYear' => $academicYear,
        ]);
    }
}