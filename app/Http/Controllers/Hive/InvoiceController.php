<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index()
    {
        $invoices = Invoice::with(['user', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Hive/Finance/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        return Inertia::render('Hive/Finance/Invoices/Create');
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'client_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'items' => 'required|array',
            'sub_total' => 'required|numeric',
            'vat' => 'nullable|numeric',
            'vat_rate' => 'nullable|numeric',
            'total_due' => 'required|numeric',
            'due_date' => 'required|date',
            'status' => 'nullable|in:pending,paid,overdue',
        ]);

        $invoice = Invoice::create([
            'number' => 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 5, '0', STR_PAD_LEFT),
            'issue_date' => now(),
            ...$validated,
        ]);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'payments']);
        return Inertia::render('Hive/Finance/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        return Inertia::render('Hive/Finance/Invoices/Edit', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'items' => 'required|array',
            'sub_total' => 'required|numeric',
            'vat' => 'nullable|numeric',
            'vat_rate' => 'nullable|numeric',
            'total_due' => 'required|numeric',
            'due_date' => 'required|date',
            'status' => 'nullable|in:pending,paid,overdue',
        ]);

        $invoice->update($validated);
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    // ---------- PDF GENERATION ----------

    /**
     * Generate Invoice PDF.
     */
    public function generatePdf(Invoice $invoice)
    {
        $data = [
            'office' => 'Finance',
            'ref' => 'HBCI/FIN/' . date('Y') . '/' . $invoice->id,
            'date' => now(),
            'invoice_number' => $invoice->number,
            'issue_date' => $invoice->issue_date,
            'due_date' => $invoice->due_date,
            'client_name' => $invoice->client_name,
            'client_contact_person' => $invoice->contact_person ?? $invoice->client_name,
            'client_address' => $invoice->address,
            'client_email' => $invoice->email,
            'client_phone' => $invoice->phone,
            'items' => $invoice->items,
            'sub_total' => $invoice->sub_total,
            'vat' => $invoice->vat ?? 0,
            'vat_rate' => $invoice->vat_rate ?? 0,
            'total_due' => $invoice->total_due,
            'bank_name' => 'Standard Lesotho Bank',
            'account_number' => '00000000',
            'branch_code' => 'Main Branch',
            'payment_reference' => $invoice->number,
            'authorised_signatory' => $this->getSignatory('finance'),
        ];

        $pdf = Pdf::loadView('pdf.documents.invoice', $data);
        return $pdf->stream('Invoice_' . $invoice->number . '.pdf');
    }

    private function getSignatory($role)
    {
        $user = \App\Models\User::role($role)->first();
        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }
}
