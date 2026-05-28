<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip — {{ $payslip->pay_period_start->format('F Y') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 3px solid #f59e0b;
        }
        .header-brand { display: flex; align-items: center; gap: 12px; }
        .header-brand img { height: 48px; }
        .header-brand-text { line-height: 1.2; }
        .header-brand-text . institute { font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #92400e; text-transform: uppercase; }
        .header-brand-text . hive { font-size: 14px; font-weight: 800; color: #1a1a2e; }
        .header-right { text-align: right; }
        .payslip-badge {
            background: #f59e0b;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 4px;
            letter-spacing: 0.05em;
        }

        /* ── Sub-header logos ── */
        .logos-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 8px 24px;
            background: #fef3c7;
            border-bottom: 1px solid #fde68a;
        }
        .logos-bar img { height: 22px; width: auto; opacity: 0.6; }
        .logos-bar img:hover { opacity: 1; }

        /* ── Company info & employee ── */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid td { padding: 10px 24px; vertical-align: top; }
        .info-grid .company-col { width: 55%; border-right: 1px solid #e5e7eb; }
        .info-grid .employee-col { width: 45%; }
        .info-block-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #92400e; margin-bottom: 4px; }
        .info-block-value { font-size: 13px; font-weight: 600; color: #1a1a2e; line-height: 1.4; }

        .period-banner {
            text-align: center;
            background: #1a1a2e;
            color: white;
            padding: 8px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        /* ── Tables ── */
        .tables-section { padding: 16px 24px; }
        .split-table { width: 100%; border-collapse: collapse; }
        .split-table td { vertical-align: top; }
        .left-col, .right-col { width: 50%; }

        table.details { width: 100%; border-collapse: collapse; font-size: 11px; }
        .details th, .details td { padding: 7px 10px; text-align: left; border-bottom: 1px solid #f3f4f6; }
        .details th { background: #f8fafc; font-weight: 600; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; }
        .details tr:last-child td { border-bottom: none; }
        .details .amount { text-align: right; font-weight: 600; }
        .details .total-row td { border-top: 2px solid #1a1a2e; background: #f8fafc; font-weight: 700; font-size: 12px; }

        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #92400e; margin-bottom: 6px; padding-left: 2px; }
        .earning-label { color: #065f46; }
        .deduction-label { color: #991b1b; }

        /* ── Net Pay ── */
        .netpay-section { padding: 0 24px 16px; }
        .netpay-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1a1a2e;
            color: white;
            padding: 14px 20px;
            border-radius: 8px;
        }
        .netpay-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: #d97706; }
        .netpay-amount { font-size: 24px; font-weight: 800; color: white; }
        .netpay-period { font-size: 10px; color: #94a3b8; margin-top: 2px; text-align: right; }

        /* ── Leave info ── */
        .leave-info { padding: 0 24px 16px; }
        .leave-box {
            border: 1px solid #fed7aa;
            background: #fffbeb;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 11px;
            color: #92400e;
        }
        .leave-box strong { font-weight: 700; }
        .leave-detail { display: inline-block; margin-right: 16px; }
        .leave-detail span { font-weight: 600; color: #1a1a2e; }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 24px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #94a3b8;
        }
        .footer-logos { display: flex; gap: 12px; align-items: center; }
        .footer-logos img { height: 16px; opacity: 0.5; }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-brand">
        <img src="{{ public_path('images/hbci-logo.png') }}" alt="HBCI">
        <div class="header-brand-text">
            <div class="institute">Honey Bee Culinary Institute</div>
            <div class="hive">The Hive — HR Portal</div>
        </div>
    </div>
    <div class="header-right">
        <div class="payslip-badge">PAYSLIP</div>
        <div style="margin-top: 4px; font-size: 10px; color: #64748b;">Generated {{ now()->format('d M Y') }}</div>
    </div>
</div>

<!-- Logos bar -->
<div class="logos-bar">
    <img src="{{ public_path('images/cchpl-alt-logo.png') }}" alt="CCHPL" title="Council for Higher Education">
    <img src="{{ public_path('images/lca-logo.png') }}" alt="LCA" title="Lesotho Chefs Association">
    <img src="{{ public_path('images/city-n-guilds-logo.png') }}" alt="City & Guilds" title="City & Guilds">
    <img src="{{ public_path('images/safa-logo.png') }}" alt="SAFA" title="South African Federation of Chefs">
    <img src="{{ public_path('images/sachef-logo.png') }}" alt="SACHED" title="SACHED">
</div>

<!-- Period Banner -->
<div class="period-banner">
    Pay Period: {{ $payslip->pay_period_start->format('d F Y') }} — {{ $payslip->pay_period_end->format('d F Y') }}
</div>

<!-- Company & Employee Info -->
<table class="info-grid">
    <tr>
        <td class="company-col">
            <div class="info-block-label">Employer</div>
            <div class="info-block-value">Honey Bee Culinary Institute</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                P.O. Box 1234, Maseru, Lesotho<br>
                Tel: +266 5888 1234 | info@hbci.co.ls
            </div>
        </td>
        <td class="employee-col">
            <div class="info-block-label">Employee</div>
            <div class="info-block-value">{{ $payslip->user->name ?? 'Unknown' }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                {{ $payslip->user->email ?? '' }}
            </div>
        </td>
    </tr>
</table>

<!-- Earnings & Deductions Tables -->
<div class="tables-section">
    <table class="split-table">
        <tr>
            <!-- Earnings -->
            <td class="left-col" style="padding-right: 12px;">
                <div class="section-title">Earnings</div>
                <table class="details">
                    @php $earnings = $payslip->earnings ?? []; @endphp
                    @if(($earnings['base_salary'] ?? 0) > 0)
                    <tr>
                        <td class="earning-label">Base Salary</td>
                        <td class="amount">LSL {{ number_format($earnings['base_salary'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(($earnings['housing_allowance'] ?? 0) > 0)
                    <tr>
                        <td class="earning-label">Housing Allowance</td>
                        <td class="amount">LSL {{ number_format($earnings['housing_allowance'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(($earnings['transport_allowance'] ?? 0) > 0)
                    <tr>
                        <td class="earning-label">Transport Allowance</td>
                        <td class="amount">LSL {{ number_format($earnings['transport_allowance'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(($earnings['medical_allowance'] ?? 0) > 0)
                    <tr>
                        <td class="earning-label">Medical Allowance</td>
                        <td class="amount">LSL {{ number_format($earnings['medical_allowance'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(($earnings['overtime'] ?? 0) > 0)
                    <tr>
                        <td class="earning-label">Overtime</td>
                        <td class="amount">LSL {{ number_format($earnings['overtime'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(($earnings['bonus'] ?? 0) > 0)
                    <tr>
                        <td class="earning-label">Bonus / Incentive</td>
                        <td class="amount">LSL {{ number_format($earnings['bonus'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>Gross Earnings</td>
                        <td class="amount">LSL {{ number_format($payslip->gross_salary, 2) }}</td>
                    </tr>
                </table>
            </td>

            <!-- Deductions -->
            <td class="right-col" style="padding-left: 12px;">
                <div class="section-title">Deductions</div>
                <table class="details">
                    @php $deductions = $payslip->deductions_breakdown ?? []; @endphp
                    @if(($deductions['tax'] ?? 0) > 0)
                    <tr>
                        <td class="deduction-label">PAYE / Tax</td>
                        <td class="amount">LSL {{ number_format($deductions['tax'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(($deductions['pension'] ?? 0) > 0)
                    <tr>
                        <td class="deduction-label">Pension Fund</td>
                        <td class="amount">LSL {{ number_format($deductions['pension'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(($deductions['leave_deduction'] ?? 0) > 0)
                    <tr>
                        <td class="deduction-label">Unpaid Leave Deduction</td>
                        <td class="amount">LSL {{ number_format($deductions['leave_deduction'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(($deductions['other'] ?? 0) > 0)
                    <tr>
                        <td class="deduction-label">Other Deduction</td>
                        <td class="amount">LSL {{ number_format($deductions['other'] ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if(empty($deductions))
                    <tr>
                        <td colspan="2" style="color: #94a3b8; font-style: italic; padding: 8px 10px;">No deductions recorded.</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>Total Deductions</td>
                        <td class="amount">LSL {{ number_format($payslip->deductions, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<!-- Net Pay -->
<div class="netpay-section">
    <div class="netpay-box">
        <div>
            <div class="netpay-label">Net Pay</div>
            <div class="netpay-period">{{ $payslip->pay_period_start->format('F Y') }}</div>
        </div>
        <div class="netpay-amount">LSL {{ number_format($payslip->net_salary, 2) }}</div>
    </div>
</div>

<!-- Leave info -->
@if($payslip->leave_days_taken > 0 || ($payslip->leave_deducted ?? 0) > 0)
<div class="leave-info">
    <div class="leave-box">
        <strong>Leave Information:</strong>
        <div class="leave-detail">Days Taken: <span>{{ $payslip->leave_days_taken }}</span></div>
        @if(($payslip->leave_deducted ?? 0) > 0)
        <div class="leave-detail">Deducted: <span>LSL {{ number_format($payslip->leave_deducted, 2) }}</span></div>
        @endif
    </div>
</div>
@endif

<!-- Footer -->
<div class="footer">
    <div>
        <strong>Honey Bee Culinary Institute — The Hive</strong> &nbsp;|&nbsp;
        Confidential document for intended recipient only.
    </div>
    <div class="footer-logos">
        <img src="{{ public_path('images/cchpl-alt-logo.png') }}" alt="">
        <img src="{{ public_path('images/lca-logo.png') }}" alt="">
        <img src="{{ public_path('images/city-n-guilds-logo.png') }}" alt="">
    </div>
</div>

</body>
</html>