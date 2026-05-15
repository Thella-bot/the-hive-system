<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function index(): Response
    {
        $years = AcademicYear::withCount('cohorts')
            ->orderByDesc('start_date')
            ->paginate(10);

        return Inertia::render('AcademicYears/Index', [
            'years' => $years,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('AcademicYears/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:50|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        AcademicYear::create($data);

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year created.');
    }

    public function edit(AcademicYear $academicYear): Response
    {
        return Inertia::render('AcademicYears/Edit', [
            'year' => $academicYear,
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:50|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $academicYear->update($data);

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year updated.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->cohorts()->exists()) {
            return back()->with('error', 'Cannot delete an academic year that has cohorts assigned to it.');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year deleted.');
    }
}
