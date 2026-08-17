<?php
declare(strict_types=1);

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
        $year = (int) $request->validated()['year'];

        $academicYear = AcademicYear::create([
            'name'       => (string) $year,
            'start_date' => "{$year}-01-01",
            'end_date'   => "{$year}-12-31",
            'is_current' => $request->boolean('is_current', false),
        ]);

        $academicYear->generateDefaultCohorts();

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
        $year = (int) $request->validated()['year'];

        $academicYear->update([
            'name'       => (string) $year,
            'start_date' => "{$year}-01-01",
            'end_date'   => "{$year}-12-31",
            'is_current' => $request->boolean('is_current', false),
        ]);

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