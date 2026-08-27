<?php

namespace App\Http\Controllers\Hive\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFilters;
use App\Models\Invoice;
use App\Models\Programme;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    use HasFilters;

    public function __construct(
        protected AuditService $audit,
    ) {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

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
                    ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Hive/Finance/Invoice/Index', [
            'invoices' => $query->paginate(20)->withQueryString(),
            'filters' => $this->getFilterInputs($request, ['status', 'academic_year', 'search']),
            'statuses' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'],
            'academicYears' => Invoice::distinct()->pluck('academic_year')->filter()->sort()->reverse()->values(),
        ]);
    }

    public function create(Request $request): Response
    {
        $users = User::with('profile')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'student_number']);

        $programmes = Programme::with('department')->orderBy('name')->get(['id', 'name', 'department_id']);

        return Inertia::render('Hive/Finance/Invoice/Create', [
            'users' => $users,
            'programmes' => $programmes,
            'types' => ['registration', 'tuition', 'uniform', 'tools', 'resource', 'examination', 'other'],
            'statuses' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'],
        ]);
    }

    public function searchStudents(Request $request): Response
    {
        $search = $request->query('q', '');

        $query = User::with('profile')
            ->where('status', 'active')
            ->orderBy('name')
            ->limit(20);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhereHas('profile', fn($pq) => $pq->where('student_number', 'like', "%{$search}%"));
            });
        }

        $students = $query->get(['id', 'name', 'email', 'student_number']);

        return Inertia::render('Hive/Finance/Invoice/Create', [
            'students' => $students,
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $invoice->load(['user', 'programme']);

        $users = User::with('profile')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $programmes = Programme::with('department')->orderBy('name')->get(['id', 'name', 'department_id']);

        return Inertia::render('Hive/Finance/Invoice/Edit', [
            'invoice' => $invoice,
            'users' => $users,
            'programmes' => $programmes,
            'types' => ['registration', 'tuition', 'uniform', 'tools', 'resource', 'examination', 'other'],
            'statuses' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'],
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
        ]);

        // Sanitize description
        $data['description'] = isset($data['description']) ? strip_tags($data['description']) : null;

        $invoice = Invoice::create($data);

        $this->audit->logCreated($invoice);

        return redirect()->route('hive.finance.invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'sometimes|in:registration,tuition,uniform,tools,resource,examination,other',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,partial,paid,overdue,cancelled',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Sanitize text fields
        $data['description'] = isset($data['description']) ? strip_tags($data['description']) : null;
        $data['notes'] = isset($data['notes']) ? strip_tags($data['notes']) : null;

        $oldValues = $invoice->getOriginal();
        $invoice->update($data);

        $this->audit->logUpdated($invoice, $oldValues);

        return redirect()->route('hive.finance.invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['user', 'programme', 'variant', 'payments.recorder']);

        return Inertia::render('Hive/Finance/Invoice/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->audit->logDeleted($invoice);

        $invoice->delete();

        return redirect()->route('hive.finance.invoices.index')->with('success', 'Invoice deleted.');
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

        $existingKeys = Invoice::where('programme_id', $data['programme_id'])
            ->where('academic_year', $data['academic_year'])
            ->where('type', $data['type'])
            ->pluck('user_id')
            ->toArray();

        $students = User::where('programme_id', $data['programme_id'])
            ->where('status', 'active')
            ->whereNotIn('id', $existingKeys)
            ->get();

        $invoices = $students->map(function ($student) use ($data, $programme, $amount) {
            return [
                'user_id' => $student->id,
                'programme_id' => $data['programme_id'],
                'variant_id' => $data['variant_id'] ?? $programme->default_variant?->id,
                'type' => $data['type'],
                'amount' => $amount,
                'academic_year' => $data['academic_year'],
                'due_date' => $data['due_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        $created = 0;
        if ($invoices->isNotEmpty()) {
            Invoice::insert($invoices->toArray());
            $created = $invoices->count();
        }

        $this->audit->log('invoice_bulk_generated', "Generated {$created} invoices for programme {$programme->name}");

        return back()->with('success', "Generated {$created} invoices.");
    }
}
