<?php

namespace App\Http\Controllers\Hive\Finance;

use App\Http\Controllers\Controller;
use App\Models\ConvectionaryIncome;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConvectionaryIncomeController extends Controller
{
    /**
     * Check if user can modify the given convectionary income.
     * Admins can modify all; finance staff can only modify their own records.
     */
    private function authorizeConvectionary(ConvectionaryIncome $convectionary): void
    {
        $user = auth()->user();

        // Admins can modify all records
        if ($user->isAdmin()) {
            return;
        }

        // Finance/HR can only modify records they recorded
        if ($convectionary->recorded_by !== $user->id) {
            abort(403, 'You can only modify convectionary income records you recorded.');
        }
    }

    /**
     * Display convectionary income list.
     */
    public function index(Request $request)
    {
        $query = ConvectionaryIncome::with('recorder')->orderByDesc('income_date');

        if ($request->has('source') && $request->source) {
            $query->where('source', $request->source);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('academic_year') && $request->academic_year) {
            $query->whereYear('income_date', $request->academic_year);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $incomes = $query->paginate(20)->withQueryString();

        $totals = ConvectionaryIncome::where('status', 'received')
            ->when($request->academic_year, fn($q) => $q->whereYear('income_date', $request->academic_year))
            ->selectRaw('source, SUM(amount) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        $totalReceived = $totals->sum();

        return Inertia::render('Hive/Finance/Convectionary/Index', [
            'incomes' => $incomes,
            'filters' => $request->only(['source', 'status', 'academic_year', 'search']),
            'sources' => ConvectionaryIncome::SOURCES,
            'statuses' => array_keys(ConvectionaryIncome::STATUSES),
            'totalReceived' => $totalReceived,
            'totalsBySource' => $totals,
        ]);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return Inertia::render('Hive/Finance/Convectionary/Create', [
            'sources' => ConvectionaryIncome::SOURCES,
            'methods' => ConvectionaryIncome::METHODS,
        ]);
    }

    /**
     * Store new convectionary income.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source' => 'required|string|in:' . implode(',', array_keys(ConvectionaryIncome::SOURCES)),
            'amount' => 'required|numeric|min:0.01',
            'income_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|in:' . implode(',', array_keys(ConvectionaryIncome::METHODS)),
            'status' => 'sometimes|string|in:' . implode(',', array_keys(ConvectionaryIncome::STATUSES)),
        ]);

        $data['recorded_by'] = auth()->id();
        $data['reference'] = 'CI-' . date('Y') . '-' . strtoupper(uniqid());

        ConvectionaryIncome::create($data);

        return redirect()->route('finance.convectionary.index')
            ->with('success', 'Convectionary income recorded successfully.');
    }

    /**
     * Show single income record.
     */
    public function show(ConvectionaryIncome $convectionary)
    {
        $this->authorizeConvectionary($convectionary);

        $convectionary->load('recorder');

        return Inertia::render('Hive/Finance/Convectionary/Show', [
            'income' => $convectionary,
        ]);
    }

    /**
     * Update income record.
     */
    public function update(Request $request, ConvectionaryIncome $convectionary): RedirectResponse
    {
        $this->authorizeConvectionary($convectionary);

        $data = $request->validate([
            'source' => 'sometimes|string|in:' . implode(',', array_keys(ConvectionaryIncome::SOURCES)),
            'amount' => 'sometimes|numeric|min:0.01',
            'income_date' => 'sometimes|date',
            'description' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|in:' . implode(',', array_keys(ConvectionaryIncome::METHODS)),
            'status' => 'sometimes|string|in:' . implode(',', array_keys(ConvectionaryIncome::STATUSES)),
        ]);

        $convectionary->update($data);

        return back()->with('success', 'Convectionary income updated successfully.');
    }

    /**
     * Delete income record.
     */
    public function destroy(ConvectionaryIncome $convectionary): RedirectResponse
    {
        $this->authorizeConvectionary($convectionary);

        $convectionary->delete();

        return redirect()->route('finance.convectionary.index')
            ->with('success', 'Convectionary income deleted successfully.');
    }
}