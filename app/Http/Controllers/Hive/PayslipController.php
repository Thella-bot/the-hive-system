<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\SalaryProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PayslipController extends Controller
{
    // ─── Index: staff sees own, admin sees all ────────────────────
    public function index(): Response
    {
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['super-admin', 'school-admin']);

        $payslips = Payslip::with('user')
            ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
            ->latest('pay_period_end')
            ->paginate(20);

        $users = $isAdmin
            ? User::query()
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'student'))
                ->with('salaryProfile')
                ->get(['id', 'name'])
            : collect();

        return Inertia::render('Hive/Payslips/Index', [
            'payslips' => $payslips,
            'users' => $users,
            'canManagePayslips' => $isAdmin,
        ]);
    }

    // ─── Admin: show create/edit form ─────────────────────────────
    public function create(): Response
    {
        $this->authorizeAdmin();

        $staff = User::query()
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'student'))
            ->with('salaryProfile')
            ->get(['id', 'name']);

        return Inertia::render('Hive/Payslips/Manage', [
            'staff' => $staff,
        ]);
    }

    public function edit(Payslip $payslip): Response
    {
        $this->authorizeAdmin();
        $this->authorizeOwnerOrAdmin($payslip);

        $staff = User::query()
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'student'))
            ->with('salaryProfile')
            ->get(['id', 'name']);

        return Inertia::render('Hive/Payslips/Manage', [
            'payslip' => $payslip->load('user'),
            'staff' => $staff,
            'isEditing' => true,
        ]);
    }

    // ─── Admin: store new payslip with full salary breakdown ───────
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'base_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'overtime' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'pension_deduction' => 'nullable|numeric|min:0',
            'leave_deducted' => 'nullable|numeric|min:0',
            'leave_days_taken' => 'nullable|integer|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
        ]);

        $user = User::findOrFail($data['user_id']);

        $earnings = [
            'base_salary' => (float) $data['base_salary'],
            'housing_allowance' => (float) ($data['housing_allowance'] ?? 0),
            'transport_allowance' => (float) ($data['transport_allowance'] ?? 0),
            'medical_allowance' => (float) ($data['medical_allowance'] ?? 0),
            'overtime' => (float) ($data['overtime'] ?? 0),
            'bonus' => (float) ($data['bonus'] ?? 0),
        ];

        $grossSalary = collect($earnings)->sum();

        $deductionsBreakdown = [
            'tax' => (float) ($data['tax_deduction'] ?? 0),
            'pension' => (float) ($data['pension_deduction'] ?? 0),
            'leave_deduction' => (float) ($data['leave_deducted'] ?? 0),
            'other' => (float) ($data['other_deduction'] ?? 0),
        ];

        $totalDeductions = collect($deductionsBreakdown)->sum();

        Payslip::create([
            'user_id' => $user->id,
            'pay_period_start' => $data['pay_period_start'],
            'pay_period_end' => $data['pay_period_end'],
            'gross_salary' => $grossSalary,
            'deductions' => $totalDeductions,
            'net_salary' => $grossSalary - $totalDeductions,
            'earnings' => $earnings,
            'deductions_breakdown' => $deductionsBreakdown,
            'leave_deducted' => $data['leave_deducted'] ?? 0,
            'leave_days_taken' => $data['leave_days_taken'] ?? 0,
        ]);

        return redirect()
            ->route('hive.payslips.index')
            ->with('success', 'Payslip created for ' . $user->name . '.');
    }

    public function update(Request $request, Payslip $payslip): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeAdmin();
        $this->authorizeOwnerOrAdmin($payslip);

        $data = $request->validate([
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'base_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'overtime' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'pension_deduction' => 'nullable|numeric|min:0',
            'leave_deducted' => 'nullable|numeric|min:0',
            'leave_days_taken' => 'nullable|integer|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
        ]);

        $earnings = [
            'base_salary' => (float) $data['base_salary'],
            'housing_allowance' => (float) ($data['housing_allowance'] ?? 0),
            'transport_allowance' => (float) ($data['transport_allowance'] ?? 0),
            'medical_allowance' => (float) ($data['medical_allowance'] ?? 0),
            'overtime' => (float) ($data['overtime'] ?? 0),
            'bonus' => (float) ($data['bonus'] ?? 0),
        ];

        $grossSalary = collect($earnings)->sum();

        $deductionsBreakdown = [
            'tax' => (float) ($data['tax_deduction'] ?? 0),
            'pension' => (float) ($data['pension_deduction'] ?? 0),
            'leave_deduction' => (float) ($data['leave_deducted'] ?? 0),
            'other' => (float) ($data['other_deduction'] ?? 0),
        ];

        $totalDeductions = collect($deductionsBreakdown)->sum();

        $payslip->update([
            'pay_period_start' => $data['pay_period_start'],
            'pay_period_end' => $data['pay_period_end'],
            'gross_salary' => $grossSalary,
            'deductions' => $totalDeductions,
            'net_salary' => $grossSalary - $totalDeductions,
            'earnings' => $earnings,
            'deductions_breakdown' => $deductionsBreakdown,
            'leave_deducted' => $data['leave_deducted'] ?? 0,
            'leave_days_taken' => $data['leave_days_taken'] ?? 0,
        ]);

        return back()->with('success', 'Payslip updated.');
    }

    // ─── Staff: generate own payslip for a month ───────────────────
    public function generate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $profile = $user->salaryProfile;

        $month = $request->input('month')
            ? \Carbon\Carbon::parse($request->input('month') . '-01')
            : now();

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $baseSalary = (float) ($profile?->base_salary ?? 0);

        $earnings = [
            'base_salary' => $baseSalary,
            'housing_allowance' => (float) ($profile?->housing_allowance ?? 0),
            'transport_allowance' => (float) ($profile?->transport_allowance ?? 0),
            'medical_allowance' => (float) ($profile?->medical_allowance ?? 0),
        ];

        $grossSalary = collect($earnings)->sum();
        $leaveDetail = $this->calculateLeaveDeductions($user, $start, $end, $baseSalary);

        $taxRate = (float) ($profile?->tax_rate ?? 0);
        $pensionRate = (float) ($profile?->pension_rate ?? 0);

        $deductionsBreakdown = [
            'tax' => round($grossSalary * ($taxRate / 100), 2),
            'pension' => round($grossSalary * ($pensionRate / 100), 2),
            'leave_deduction' => $leaveDetail['deduction'],
            'other' => 0,
        ];

        $totalDeductions = collect($deductionsBreakdown)->sum();
        $netSalary = $grossSalary - $totalDeductions;

        $existing = Payslip::where('user_id', $user->id)
            ->whereDate('pay_period_start', $start)
            ->first();

        if ($existing) {
            $existing->update([
                'gross_salary' => $grossSalary,
                'deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'earnings' => $earnings,
                'deductions_breakdown' => $deductionsBreakdown,
                'leave_deducted' => $leaveDetail['deduction'],
                'leave_days_taken' => $leaveDetail['days_taken'],
                'leave_taken_detail' => $leaveDetail['detail'],
            ]);
            $msg = 'Payslip for ' . $month->format('F Y') . ' updated.';
        } else {
            Payslip::create([
                'user_id' => $user->id,
                'pay_period_start' => $start,
                'pay_period_end' => $end,
                'gross_salary' => $grossSalary,
                'deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'earnings' => $earnings,
                'deductions_breakdown' => $deductionsBreakdown,
                'leave_deducted' => $leaveDetail['deduction'],
                'leave_days_taken' => $leaveDetail['days_taken'],
                'leave_taken_detail' => $leaveDetail['detail'],
            ]);
            $msg = 'Payslip for ' . $month->format('F Y') . ' generated.';
        }

        return redirect()->route('hive.payslips.index')->with('success', $msg);
    }

    // ─── Admin: batch generate for all staff ───────────────────────
    public function generateBatch(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeAdmin();

        $month = $request->input('month')
            ? \Carbon\Carbon::parse($request->input('month') . '-01')
            : now();

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $staff = User::query()
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'student'))
            ->with('salaryProfile')
            ->get();

        foreach ($staff as $user) {
            $profile = $user->salaryProfile;
            $baseSalary = (float) ($profile?->base_salary ?? 0);

            $earnings = [
                'base_salary' => $baseSalary,
                'housing_allowance' => (float) ($profile?->housing_allowance ?? 0),
                'transport_allowance' => (float) ($profile?->transport_allowance ?? 0),
                'medical_allowance' => (float) ($profile?->medical_allowance ?? 0),
            ];

            $grossSalary = collect($earnings)->sum();
            $leaveDetail = $this->calculateLeaveDeductions($user, $start, $end, $baseSalary);

            $taxRate = (float) ($profile?->tax_rate ?? 0);
            $pensionRate = (float) ($profile?->pension_rate ?? 0);

            $deductionsBreakdown = [
                'tax' => round($grossSalary * ($taxRate / 100), 2),
                'pension' => round($grossSalary * ($pensionRate / 100), 2),
                'leave_deduction' => $leaveDetail['deduction'],
                'other' => 0,
            ];

            $totalDeductions = collect($deductionsBreakdown)->sum();
            $netSalary = $grossSalary - $totalDeductions;

            $existing = Payslip::where('user_id', $user->id)
                ->whereDate('pay_period_start', $start)
                ->first();

            if ($existing) {
                $existing->update([
                    'gross_salary' => $grossSalary,
                    'deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'earnings' => $earnings,
                    'deductions_breakdown' => $deductionsBreakdown,
                    'leave_deducted' => $leaveDetail['deduction'],
                    'leave_days_taken' => $leaveDetail['days_taken'],
                    'leave_taken_detail' => $leaveDetail['detail'],
                ]);
            } else {
                Payslip::create([
                    'user_id' => $user->id,
                    'pay_period_start' => $start,
                    'pay_period_end' => $end,
                    'gross_salary' => $grossSalary,
                    'deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'earnings' => $earnings,
                    'deductions_breakdown' => $deductionsBreakdown,
                    'leave_deducted' => $leaveDetail['deduction'],
                    'leave_days_taken' => $leaveDetail['days_taken'],
                    'leave_taken_detail' => $leaveDetail['detail'],
                ]);
            }
        }

        return redirect()
            ->route('hive.payslips.index')
            ->with('success', 'Payslips generated for ' . $staff->count() . ' staff members for ' . $month->format('F Y') . '.');
    }

    // ─── Download PDF ──────────────────────────────────────────────
    public function download(Payslip $payslip)
    {
        if (!Auth::user()->hasAnyRole(['super-admin', 'school-admin']) && $payslip->user_id !== Auth::id()) {
            abort(403);
        }

        $payslip->load('user');

        $pdf = Pdf::loadView('pdf.payslip', ['payslip' => $payslip]);

        return $pdf->download('payslip-' . $payslip->user->name . '-' . $payslip->pay_period_end->format('Y-m') . '.pdf');
    }

    // ─── Admin: delete payslip ────────────────────────────────────
    public function destroy(Payslip $payslip): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeAdmin();

        $payslip->delete();

        return redirect()->route('hive.payslips.index')->with('success', 'Payslip deleted.');
    }

    // ─── Private helpers ───────────────────────────────────────────
    private function authorizeAdmin(): void
    {
        if (!Auth::user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403, 'Only administrators can perform this action.');
        }
    }

    private function authorizeOwnerOrAdmin(Payslip $payslip): void
    {
        if (!Auth::user()->hasAnyRole(['super-admin', 'school-admin']) && $payslip->user_id !== Auth::id()) {
            abort(403);
        }
    }

    /**
     * Calculate prorated salary deduction for leave taken during a pay period.
     * Unpaid leave days (beyond annual leave balance) are deducted at a daily rate.
     */
    private function calculateLeaveDeductions(User $user, \Carbon\Carbon $start, \Carbon\Carbon $end, float $baseSalary): array
    {
        $workingDaysPerMonth = 22;
        $dailyRate = $workingDaysPerMonth > 0 ? $baseSalary / $workingDaysPerMonth : 0;

        $leaves = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(fn($q2) => $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end));
            })
            ->get();

        $unpaidDays = 0;
        $detail = [];

        foreach ($leaves as $leave) {
            $leaveStart = $leave->start_date->max($start);
            $leaveEnd = $leave->end_date->min($end);
            $days = $leaveStart->diffInDays($leaveEnd) + 1;

            if ($leave->type === 'annual') {
                $profile = $user->profile;
                if ($profile && $profile->leave_balance > 0) {
                    $covered = min($days, (int) $profile->leave_balance);
                    $unpaidDays += $days - $covered;
                } else {
                    $unpaidDays += $days;
                }
            } else {
                $unpaidDays += $days;
            }

            $detail[] = [
                'type' => $leave->type,
                'days' => $days,
                'period' => $leaveStart->format('d M') . ' - ' . $leaveEnd->format('d M'),
            ];
        }

        $deduction = round($unpaidDays * $dailyRate, 2);

        return [
            'days_taken' => $leaves->sum(fn($l) => $l->days()),
            'days_unpaid' => $unpaidDays,
            'deduction' => $deduction,
            'detail' => $detail,
        ];
    }
}