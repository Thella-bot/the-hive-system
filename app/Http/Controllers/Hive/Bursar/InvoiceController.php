<?php

namespace App\Http\Controllers\Hive\Bursar;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFilters;
use App\Models\Invoice;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    use HasFilters;

    public function index(Request $request): Response
    {
        $query = Invoice::with(['user', 'programme'])
            ->orderByDesc('created_at');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('academic_year') && $request->academic_year) {
            $query->where('academic_year', $request->academic_year);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('user_number', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Bursar/Invoice/Index', [
            'invoices' => $query->paginate(20)->withQueryString(),
            'filters' => $this->getFilterInputs($request, ['status', 'academic_year', 'search']),
            'statuses' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'],
            'academicYears' => Invoice::distinct()->pluck('academic_year')->filter()->sort()->reverse()->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'programme_id' => 'required|exists:programmes,id',
            'variant_id' => 'nullable|exists:programme_variants,id',
            'type' => 'required|in:registration,tuition,uniform,tools,resource,examination,other',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'due_date' => 'nullable|date',
            'academic_year' => 'required|string',
            'semester' => 'required|integer|min:1|max:3',
        ]);

        Invoice::create($data);

        return back()->with('success', 'Invoice created successfully.');
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'sometimes|in:registration,tuition,uniform,tools,resource,examination,other',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,partial,paid,overdue,cancelled',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($data);

        return back()->with('success', 'Invoice updated successfully.');
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['user', 'programme', 'variant', 'payments.recorder']);

        return Inertia::render('Bursar/Invoice/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('bursar.invoices.index')->with('success', 'Invoice deleted.');
    }

    /**
     * Generate invoices for all students in a programme for a given semester.
     */
    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'variant_id' => 'nullable|exists:programme_variants,id',
            'type' => 'required|in:registration,tuition,uniform,tools,resource,examination,other',
            'academic_year' => 'required|string',
            'semester' => 'required|integer|min:1|max:3',
            'due_date' => 'nullable|date',
        ]);

        $programme = Programme::findOrFail($data['programme_id']);

        $amount = match ($data['type']) {
            'registration' => $programme->registration_fee,
            'tuition' => $programme->monthly_fee,
            'uniform' => $programme->uniform_fee,
            'tools' => $programme->tools_cost,
            'resource' => $programme->academic_resource_fee,
            default => 0,
        };

        if ($amount <= 0) {
            return back()->with('error', 'No fee amount defined for this invoice type.');
        }

        // Get enrolled students for this programme
        $students = User::where('programme_id', $data['programme_id'])
            ->where('status', 'active')
            ->get();

        $created = 0;
        foreach ($students as $student) {
            // Skip if invoice already exists for this type/academic_year/semester
            $exists = Invoice::where('user_id', $student->id)
                ->where('programme_id', $data['programme_id'])
                ->where('academic_year', $data['academic_year'])
                ->where('semester', $data['semester'])
                ->where('type', $data['type'])
                ->exists();

            if (! $exists) {
                Invoice::create([
                    'user_id' => $student->id,
                    'programme_id' => $data['programme_id'],
                    'variant_id' => $data['variant_id'] ?? $programme->default_variant?->id,
                    'type' => $data['type'],
                    'amount' => $amount,
                    'academic_year' => $data['academic_year'],
                    'semester' => $data['semester'],
                    'due_date' => $data['due_date'],
                ]);
                $created++;
            }
        }

        return back()->with('success', "Generated {$created} invoices.");
    }
}