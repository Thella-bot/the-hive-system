<?php

namespace App\Http\Controllers\Hive\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFilters;
use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    use HasFilters;

    public function index(Request $request): Response
    {
        $query = Payment::query()
            ->with(['user', 'invoice', 'recorder'])
            ->orderByDesc('created_at');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('user_number', 'like', "%{$search}%");
            });
        }

        $this->applyFilters($query, $request, [
            'date_from' => true,
            'date_to' => true,
            'dateColumn' => 'payment_date',
        ]);

        return Inertia::render('Hive/Finance/Payment/Index', [
            'payments' => $query->paginate(20)->withQueryString(),
            'filters' => $this->getFilterInputs($request, ['status', 'payment_method', 'search', 'date_from', 'date_to']),
            'statuses' => ['pending', 'completed', 'failed', 'refunded'],
            'methods' => ['cash', 'bank_transfer', 'mobile_money', 'card', 'other'],
        ]);
    }

    public function create(Request $request): Response
    {
        $invoices = Invoice::with(['user', 'programme'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Hive/Finance/Payment/Create', [
            'invoices' => $invoices,
            'methods' => ['cash', 'bank_transfer', 'mobile_money', 'card', 'other'],
            'statuses' => ['pending', 'completed', 'failed', 'refunded'],
            'selectedInvoiceId' => $request->query('invoice_id'),
        ]);
    }

    public function edit(Payment $payment): Response
    {
        $payment->load(['user', 'invoice']);

        $invoices = Invoice::with('user')->orderBy('created_at', 'desc')->get(['id', 'invoice_number', 'user_id', 'amount', 'status']);

        return Inertia::render('Hive/Finance/Payment/Edit', [
            'payment' => $payment,
            'invoices' => $invoices,
            'methods' => ['cash', 'bank_transfer', 'mobile_money', 'card', 'other'],
            'statuses' => ['pending', 'completed', 'failed', 'refunded'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,card,other',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'proof_path' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($data['invoice_id']);
        $data['user_id'] = $invoice->user_id;

        if (! isset($data['payment_date'])) {
            $data['payment_date'] = now();
        }

        $payment = Payment::create($data);

        // Auto-verify if amount covers the balance
        if ($payment->amount >= $invoice->balance) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        } elseif ($invoice->total_paid > 0) {
            $invoice->update(['status' => 'partial']);
        }

        return redirect()->route('hive.finance.payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|in:cash,bank_transfer,mobile_money,card,other',
            'payment_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,completed,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $payment->update($data);

        // Update invoice status if payment status changed
        if (isset($data['status'])) {
            $invoice = $payment->invoice;
            if ($payment->is_completed || $data['status'] === 'completed') {
                if ($payment->amount >= $invoice->balance) {
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                } else {
                    $invoice->update(['status' => 'partial']);
                }
            }
        }

        return back()->with('success', 'Payment updated successfully.');
    }

    public function show(Payment $payment): Response
    {
        $payment->load(['user', 'invoice', 'recorder']);

        return Inertia::render('Hive/Finance/Payment/Show', [
            'payment' => $payment,
        ]);
    }

    public function downloadReceipt(Payment $payment)
    {
        $payment->load(['user', 'invoice']);

        $invoice = $payment->invoice;
        $programme = $invoice?->programme;
        $studentNumber = $payment->user?->student_number ?? $payment->user?->user_number ?? 'N/A';

        $data = [
            'receipt_number' => $payment->payment_reference,
            'date' => $payment->payment_date ?? $payment->created_at,
            'payer_name' => $payment->user?->name ?? 'N/A',
            'student_number' => $studentNumber,
            'programme_name' => $programme?->name ?? 'N/A',
            'items' => [[
                'description' => $invoice ? ($invoice->description ?? 'Tuition / fee payment') : 'Payment received',
                'qty' => 1,
                'unit_price' => (float) $payment->amount,
                'total' => (float) $payment->amount,
            ]],
            'sub_total' => (float) $payment->amount,
            'discount' => 0,
            'total_paid' => (float) $payment->amount,
            'payment_method' => $this->formatPaymentMethod($payment->payment_method),
            'bank_ref' => $payment->notes ?? 'N/A',
            'academic_year' => $invoice?->academic_year ?? date('Y'),
            'cohort' => $invoice?->semester ? 'Semester ' . $invoice->semester : 'N/A',
            'amount_words' => $this->numberToWords((float) $payment->amount),
            'cashier_name' => $this->getSignatory('finance'),
        ];

        $pdf = Pdf::loadView('pdf.documents.payment_receipt', $data);

        return $pdf->download('Receipt_' . $payment->payment_reference . '.pdf');
    }

    private function formatPaymentMethod(string $method): string
    {
        return match ($method) {
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'card' => 'Card',
            'other' => 'Other',
            default => 'Cash',
        };
    }

    private function numberToWords(int|float $number): string
    {
        $amount = number_format($number, 2);
        $parts = explode('.', $amount);
        $dollars = (int) $parts[0];
        $cents = (int) $parts[1];

        if ($dollars === 0 && $cents === 0) {
            return 'Zero';
        }

        $result = '';
        if ($dollars > 0) {
            $result .= $this->convertNumberToWords($dollars);
        }
        if ($cents > 0) {
            $result .= ' and ' . $this->convertNumberToWords($cents) . ' cents';
        }

        return trim($result);
    }

    private function convertNumberToWords(int $num): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];

        if ($num < 10) {
            return $ones[$num];
        }
        if ($num < 20) {
            return $teens[$num - 10];
        }
        if ($num < 100) {
            return $tens[(int) floor($num / 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
        }
        if ($num < 1000) {
            return $ones[(int) floor($num / 100)] . ' Hundred' . ($num % 100 ? ' ' . $this->convertNumberToWords($num % 100) : '');
        }

        return 'Number too large';
    }

    private function getSignatory($role)
    {
        $user = \App\Models\User::role($role)->first();
        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        // Restore invoice status if payment was completed
        if ($payment->is_completed && $payment->invoice) {
            $invoice = $payment->invoice;
            $payment->delete();

            $remaining = $invoice->payments()->where('status', 'completed')->sum('amount');
            if ($remaining <= 0) {
                $invoice->update(['status' => 'pending', 'paid_at' => null]);
            } elseif ($remaining < $invoice->amount) {
                $invoice->update(['status' => 'partial']);
            }
        } else {
            $payment->delete();
        }

        return redirect()->route('hive.finance.payments.index')->with('success', 'Payment deleted.');
    }

    /**
     * Verify a pending payment.
     */
    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $payment->update([
            'status' => 'completed',
            'recorded_at' => now(),
        ]);

        $invoice = $payment->invoice;
        if ($payment->amount >= $invoice->balance) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        return back()->with('success', 'Payment verified.');
    }
}
