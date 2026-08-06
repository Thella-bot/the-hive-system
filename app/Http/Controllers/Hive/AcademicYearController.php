<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
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

        return Inertia::render('Hive/AcademicYears/Index', [
            'years' => $years,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Hive/AcademicYears/Create');
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $year = AcademicYear::create($request->validated());

        $year->generateDefaultCohorts();

        return redirect()->route('hive.academic-years.index')
            ->with('success', 'Academic year created with default cohorts.');
    }

    public function edit(AcademicYear $academicYear): Response
    {
        return Inertia::render('Hive/AcademicYears/Edit', [
            'year' => $academicYear,
        ]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->update($request->validated());

        return redirect()->route('hive.academic-years.index')
            ->with('success', 'Academic year updated.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->cohorts()->exists()) {
            return back()->with('error', 'Cannot delete an academic year that has cohorts assigned to it.');
        }

        $academicYear->delete();

        return redirect()->route('hive.academic-years.index')
            ->with('success', 'Academic year deleted.');
    }
}