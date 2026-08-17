<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\GeneratesDocumentPdfs;
use App\Models\Payment;
use App\Models\Invoice;
use App\Services\NumberToWords;
use App\Services\SignatoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    use GeneratesDocumentPdfs;
    public function __construct(protected SignatoryService $signatory)
    {
        $this->authorizeResource(Payment::class, 'payment');
    }

    /**
     * Display a listing of payments.
     */
    public function index()
    {
        $payments = Payment::with(['user', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Hive/Finance/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        $invoices = Invoice::where('status', 'pending')->paginate(20);
        return Inertia::render('Hive/Finance/Payments/Create', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,cheque,bank_transfer,mpesa,ecocash',
            'reference' => 'nullable|string|max:255',
            'payer_name' => 'required|string|max:255',
            'student_number' => 'nullable|string',
            'programme_name' => 'nullable|string',
            'items' => 'nullable|array',
            'sub_total' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'bank_ref' => 'nullable|string',
            'academic_year' => 'nullable|string',
            'cohort' => 'nullable|string',
        ]);

        $validated['receipt_number'] = 'RCPT-' . date('Y') . '-' . str_pad(
            (int) Payment::lockForUpdate()->max('id') + 1,
            5,
            '0',
            STR_PAD_LEFT
        );

        $payment = Payment::create($validated);

        if ($payment->invoice_id) {
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice && $invoice->total_due <= $payment->amount) {
                $invoice->update(['status' => 'paid']);
            }
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['user', 'invoice']);
        return Inertia::render('Hive/Finance/Payments/Show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        $invoices = Invoice::all();
        return Inertia::render('Hive/Finance/Payments/Edit', [
            'payment' => $payment,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,cheque,bank_transfer,mpesa,ecocash',
            'reference' => 'nullable|string|max:255',
            'payer_name' => 'required|string|max:255',
            'student_number' => 'nullable|string',
            'programme_name' => 'nullable|string',
            'items' => 'nullable|array',
            'sub_total' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'bank_ref' => 'nullable|string',
            'academic_year' => 'nullable|string',
            'cohort' => 'nullable|string',
        ]);

        $payment->update($validated);
        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    // ---------- PDF GENERATION ----------

    /**
     * Generate Payment Receipt PDF.
     */
    public function generateReceipt(Payment $payment)
    {
        $data = [
            'office' => config('institution.finance_office'),
            'ref' => config('institution.abbreviation') . '/FIN/' . date('Y') . '/' . $payment->id,
            'date' => $payment->created_at,
            'receipt_number' => $payment->receipt_number,
            'payer_name' => $payment->payer_name,
            'student_number' => $payment->student_number ?? 'N/A',
            'programme_name' => $payment->programme_name ?? 'N/A',
            'items' => $payment->items ?? [
                [
                    'description' => 'Payment Received',
                    'qty' => 1,
                    'unit_price' => $payment->amount,
                    'total' => $payment->amount,
                ]
            ],
            'sub_total' => $payment->sub_total ?? $payment->amount,
            'discount' => $payment->discount ?? 0,
            'total_paid' => $payment->amount,
            'payment_method' => $payment->method,
            'bank_ref' => $payment->bank_ref ?? '',
            'academic_year' => $payment->academic_year ?? date('Y'),
            'cohort' => $payment->cohort ?? '',
            'amount_words' => NumberToWords::convert($payment->amount),
            'cashier_name' => $this->signatory->get('finance'),
        ];

        return $this->generatePdf('pdf.documents.payment_receipt', $data, 'Receipt_' . $payment->receipt_number . '.pdf', $payment->user_id);
    }
}
