<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Budget;
use App\Models\ConvectionaryIncome;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Collection;

class FinancialReportService
{
    public function dashboard(string $academicYear): array
    {
        $income = $this->buildIncomeData($academicYear);
        $expenses = $this->buildExpenseData($academicYear);
        $invoices = $this->buildInvoiceData($academicYear);
        $budget = $this->buildBudgetData($academicYear);
        $convectionary = $this->buildConvectionaryData($academicYear);
        $metrics = $this->buildMetrics($income, $expenses, $invoices, $budget);
        $expensesByCategory = $this->buildExpensesByCategory($academicYear);
        $recentPayments = $this->buildRecentPayments();
        $recentExpenses = $this->buildRecentExpenses();

        return [
            'academicYear' => $academicYear,
            'income' => $income,
            'expenses' => $expenses,
            'invoices' => $invoices,
            'budget' => $budget,
            'convectionary' => $convectionary,
            'metrics' => $metrics,
            'expensesByCategory' => $expensesByCategory,
            'recentPayments' => $recentPayments,
            'recentExpenses' => $recentExpenses,
        ];
    }

    public function income(array $filters): array
    {
        $query = Payment::with(['user', 'invoice', 'recorder'])
            ->where('status', 'completed')
            ->orderByDesc('payment_date');

        $this->applyDateFilters($query, $filters);
        $this->applyPaymentMethodFilter($query, $filters);

        $payments = $query->paginate(20)->withQueryString();

        $totalAmount = Payment::where('status', 'completed')
            ->when($filters['academic_year'] ?? null, fn($q) => $q->whereYear('payment_date', $filters['academic_year']))
            ->when($filters['date_from'] ?? null, fn($q) => $q->whereDate('payment_date', '>=', $filters['date_from']))
            ->when($filters['date_to'] ?? null, fn($q) => $q->whereDate('payment_date', '<=', $filters['date_to']))
            ->sum('amount');

        return [
            'payments' => $payments,
            'filters' => $filters,
            'totalAmount' => $totalAmount,
            'methods' => ['cash', 'bank_transfer', 'mobile_money', 'card', 'other'],
        ];
    }

    public function expenses(array $filters): array
    {
        $query = Expense::with(['user', 'category', 'vendor', 'budget'])
            ->orderByDesc('expense_date');

        $this->applyDateFilters($query, $filters);
        $this->applyStatusFilter($query, $filters);
        $this->applyCategoryFilter($query, $filters);

        $expenses = $query->paginate(20)->withQueryString();

        $totalAmount = Expense::whereIn('status', ['approved', 'paid'])
            ->when($filters['academic_year'] ?? null, fn($q) => $q->whereYear('expense_date', $filters['academic_year']))
            ->when($filters['date_from'] ?? null, fn($q) => $q->whereDate('expense_date', '>=', $filters['date_from']))
            ->when($filters['date_to'] ?? null, fn($q) => $q->whereDate('expense_date', '<=', $filters['date_to']))
            ->sum('amount');

        return [
            'expenses' => $expenses,
            'filters' => $filters,
            'totalAmount' => $totalAmount,
            'statuses' => ['pending', 'approved', 'rejected', 'paid', 'cancelled'],
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
        ];
    }

    public function studentLedger(int $userId): array
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

        return [
            'user' => $user,
            'invoices' => $invoices,
            'payments' => $payments,
            'summary' => [
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'balance' => $balance,
            ],
        ];
    }

    public function ageAnalysis(string $academicYear): array
    {
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

        return [
            'invoices' => $invoices,
            'byAgeBracket' => $byAgeBracket,
            'totalOverdue' => $totalOverdue,
            'academicYear' => $academicYear,
        ];
    }

    private function buildIncomeData(string $academicYear): array
    {
        $totalIncome = Payment::where('status', 'completed')
            ->whereYear('payment_date', $academicYear)
            ->sum('amount');

        $monthlyIncome = Payment::where('status', 'completed')
            ->whereYear('payment_date', $academicYear)
            ->selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'total' => $totalIncome,
            'monthly' => $monthlyIncome,
        ];
    }

    private function buildExpenseData(string $academicYear): array
    {
        $totalExpenses = Expense::whereIn('status', ['approved', 'paid'])
            ->whereYear('expense_date', $academicYear)
            ->sum('amount');

        $monthlyExpenses = Expense::whereIn('status', ['approved', 'paid'])
            ->whereYear('expense_date', $academicYear)
            ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'total' => $totalExpenses,
            'monthly' => $monthlyExpenses,
        ];
    }

    private function buildInvoiceData(string $academicYear): array
    {
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

        return [
            'total_invoiced' => $totalInvoiced,
            'total_collected' => $totalCollected,
            'pending_count' => $pendingInvoices,
            'overdue_count' => $overdueInvoices,
            'by_type' => $invoicesByType,
        ];
    }

    private function buildBudgetData(string $academicYear): array
    {
        $activeBudgets = Budget::where('academic_year', $academicYear)
            ->where('status', 'active')
            ->get();

        $totalBudgetAllocated = $activeBudgets->sum('allocated_amount');
        $totalBudgetSpent = $activeBudgets->sum(function ($budget) {
            return $budget->expenses()
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');
        });

        $budgetUtilization = $totalBudgetAllocated > 0 ? round(($totalBudgetSpent / $totalBudgetAllocated) * 100, 2) : 0;

        return [
            'total_allocated' => $totalBudgetAllocated,
            'total_spent' => $totalBudgetSpent,
            'utilization' => $budgetUtilization,
        ];
    }

    private function buildConvectionaryData(string $academicYear): array
    {
        $totalConvectionary = ConvectionaryIncome::where('status', 'received')
            ->whereYear('income_date', $academicYear)
            ->sum('amount');

        $convectionaryBySource = ConvectionaryIncome::where('status', 'received')
            ->whereYear('income_date', $academicYear)
            ->selectRaw('source, SUM(amount) as total')
            ->groupBy('source')
            ->pluck('total', 'source')
            ->toArray();

        return [
            'total' => $totalConvectionary,
            'by_source' => $convectionaryBySource,
        ];
    }

    private function buildMetrics(array $income, array $expenses, array $invoices, array $budget): array
    {
        $netPosition = $income['total'] - $expenses['total'];
        $collectionRate = $invoices['total_invoiced'] > 0 ? round(($invoices['total_collected'] / $invoices['total_invoiced']) * 100, 2) : 0;

        return [
            'net_position' => $netPosition,
            'collection_rate' => $collectionRate,
        ];
    }

    private function buildExpensesByCategory(string $academicYear): Collection
    {
        return Expense::whereIn('status', ['approved', 'paid'])
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
    }

    private function buildRecentPayments(): Collection
    {
        return Payment::with(['user', 'invoice'])
            ->where('status', 'completed')
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();
    }

    private function buildRecentExpenses(): Collection
    {
        return Expense::with(['user', 'category'])
            ->whereIn('status', ['approved', 'paid'])
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();
    }

    private function applyDateFilters($query, array $filters): void
    {
        if (!empty($filters['academic_year'])) {
            $query->whereYear('payment_date', $filters['academic_year']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('payment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('payment_date', '<=', $filters['date_to']);
        }
    }

    private function applyStatusFilter($query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    private function applyCategoryFilter($query, array $filters): void
    {
        if (!empty($filters['category_id'])) {
            $query->where('expense_category_id', $filters['category_id']);
        }
    }

    private function applyPaymentMethodFilter($query, array $filters): void
    {
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
    }
}
