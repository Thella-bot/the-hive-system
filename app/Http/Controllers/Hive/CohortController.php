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
        $this->authorize('viewAny', Cohort::class);

        $cohorts = Cohort::with(['department', 'academicYear'])
            ->withCount('students')
            ->latest()
            ->paginate(12);

        return Inertia::render('Hive/Cohorts/Index', [
            'cohorts'       => $cohorts,
            'departments'   => Department::academic()->active()->select('id', 'name', 'color')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->select('id', 'name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Cohort::class);

        return Inertia::render('Hive/Cohorts/Create', [
            'departments'   => Department::academic()->active()->select('id', 'name', 'color')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->select('id', 'name', 'is_current')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Cohort::class);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'department_id'    => ['required', 'exists:departments,id', function ($attribute, $value, $fail) {
                $department = \App\Models\Department::find($value);
                if (!$department || !$department->is_academic) {
                    $fail('The selected department is not eligible for cohorts.');
                }
            }],
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_students'     => 'required|integer|min:1|max:200',
            'is_active'        => 'boolean',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after:start_date',
        ]);

        // Sanitize inputs
        $data['name'] = strip_tags($data['name']);

        Cohort::create($data);

        return redirect()->route('hive.cohorts.index')
            ->with('success', 'Cohort created successfully.');
    }

    public function show(Cohort $cohort): Response
    {
        $this->authorize('view', $cohort);

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
        $this->authorize('update', $cohort);

        return Inertia::render('Hive/Cohorts/Edit', [
            'cohort'        => $cohort,
            'departments'   => Department::academic()->active()->select('id', 'name', 'color')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->select('id', 'name', 'is_current')->get(),
        ]);
    }

    public function update(Request $request, Cohort $cohort): RedirectResponse
    {
        $this->authorize('update', $cohort);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'department_id'    => ['required', 'exists:departments,id', function ($attribute, $value, $fail) {
                $department = \App\Models\Department::find($value);
                if (!$department || !$department->is_academic) {
                    $fail('The selected department is not eligible for cohorts.');
                }
            }],
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_students'     => 'required|integer|min:1|max:200',
            'is_active'        => 'boolean',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after:start_date',
        ]);

        // Sanitize inputs
        $data['name'] = strip_tags($data['name']);

        $cohort->update($data);

        return redirect()->route('hive.cohorts.show', $cohort)
            ->with('success', 'Cohort updated successfully.');
    }

    public function destroy(Cohort $cohort): RedirectResponse
    {
        $this->authorize('delete', $cohort);

        if ($cohort->students()->exists()) {
            return back()->with('error', 'Cannot delete a cohort that has students enrolled.');
        }

        $cohort->delete();

        return redirect()->route('hive.cohorts.index')
            ->with('success', 'Cohort deleted.');
    }
}