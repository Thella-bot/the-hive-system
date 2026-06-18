<?php

namespace App\Http\Controllers\Hive\Bursar;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFilters;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    use HasFilters;

    public function index(Request $request): Response
    {
        $query = Payment::with(['user', 'invoice', 'recorder'])
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

        return Inertia::render('Bursar/Payment/Index', [
            'payments' => $query->paginate(20)->withQueryString(),
            'filters' => $this->getFilterInputs($request, ['status', 'payment_method', 'search', 'date_from', 'date_to']),
            'statuses' => ['pending', 'completed', 'failed', 'refunded'],
            'methods' => ['cash', 'bank_transfer', 'mobile_money', 'card', 'other'],
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

        return back()->with('success', 'Payment recorded successfully.');
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

        return Inertia::render('Bursar/Payment/Show', [
            'payment' => $payment,
        ]);
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

        return redirect()->route('bursar.payments.index')->with('success', 'Payment deleted.');
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