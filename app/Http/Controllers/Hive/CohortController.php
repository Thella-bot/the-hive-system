<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CohortController extends Controller
{
    public function index(): Response
    {
        $cohorts = Cohort::with(['department', 'academicYear'])
            ->withCount('students')
            ->latest()
            ->paginate(12);

        return Inertia::render('Hive/Cohorts/Index', [
            'cohorts'       => $cohorts,
            'departments'   => Department::active()->select('id', 'name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->select('id', 'name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Hive/Cohorts/Create', [
            'departments'   => Department::active()->select('id', 'name', 'color')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->select('id', 'name', 'is_current')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'department_id'    => 'required|exists:departments,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_students'     => 'required|integer|min:1|max:200',
            'is_active'        => 'boolean',
        ]);

        Cohort::create($data);

        return redirect()->route('hive.cohorts.index')
            ->with('success', 'Cohort created successfully.');
    }

    public function show(Cohort $cohort): Response
    {
        $cohort->load([
            'department',
            'academicYear',
            'students.user',
        ]);

        return Inertia::render('Hive/Cohorts/Show', [
            'cohort' => $cohort,
        ]);
    }

    public function edit(Cohort $cohort): Response
    {
        return Inertia::render('Hive/Cohorts/Edit', [
            'cohort'        => $cohort,
            'departments'   => Department::active()->select('id', 'name', 'color')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->select('id', 'name', 'is_current')->get(),
        ]);
    }

    public function update(Request $request, Cohort $cohort): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'department_id'    => 'required|exists:departments,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_students'     => 'required|integer|min:1|max:200',
            'is_active'        => 'boolean',
        ]);

        $cohort->update($data);

        return redirect()->route('hive.cohorts.show', $cohort)
            ->with('success', 'Cohort updated successfully.');
    }

    public function destroy(Cohort $cohort): RedirectResponse
    {
        if ($cohort->students()->exists()) {
            return back()->with('error', 'Cannot delete a cohort that has students enrolled.');
        }

        $cohort->delete();

        return redirect()->route('hive.cohorts.index')
            ->with('success', 'Cohort deleted.');
    }
}