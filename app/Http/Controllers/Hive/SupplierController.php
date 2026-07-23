<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return Inertia::render('Hive/Suppliers/Index', ['suppliers' => $suppliers]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contract_expiry' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        Supplier::create($data);
        return back()->with('success', 'Supplier added.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contract_expiry' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $supplier->update($data);
        return back()->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $supplier->delete();
        return back()->with('success', 'Supplier removed.');
    }
}
