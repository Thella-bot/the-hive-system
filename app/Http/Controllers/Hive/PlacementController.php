<?php

declare(strict_types=1);

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Placement;
use App\Models\User;
use App\Services\SignatoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PlacementController extends Controller
{
    public function __construct(protected SignatoryService $signatory)
    {
        $this->authorizeResource(Placement::class, 'placement');
    }

    public function index()
    {
        $placements = Placement::with('student.profile')->paginate(15);

        return inertia('Hive/Placements/Index', ['placements' => $placements]);
    }

    public function create()
    {
        $students = User::role('student')->get();

        return inertia('Hive/Placements/Create', ['students' => $students]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'programme_id' => 'required|exists:programmes,id',
            'organisation_name' => 'required|string|max:255',
            'organisation_address' => 'required|string',
            'supervisor_name' => 'required|string',
            'supervisor_contact' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'duration' => 'required|string',
            'type' => 'required|in:Compulsory,Elective',
            'status' => 'nullable|in:pending,active,completed,cancelled',
            'learning_objectives' => 'nullable|string',
        ]);

        Placement::create($validated);

        return redirect()->route('hive.placements.index')->with('success', 'Placement created.');
    }

    public function show(Placement $placement)
    {
        return inertia('Hive/Placements/Show', ['placement' => $placement]);
    }

    public function edit(Placement $placement)
    {
        $students = User::role('student')->get();

        return inertia('Hive/Placements/Edit', ['placement' => $placement, 'students' => $students]);
    }

    public function update(Request $request, Placement $placement)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'programme_id' => 'required|exists:programmes,id',
            'organisation_name' => 'required|string|max:255',
            'organisation_address' => 'required|string',
            'supervisor_name' => 'required|string',
            'supervisor_contact' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'duration' => 'required|string',
            'type' => 'required|in:Compulsory,Elective',
            'status' => 'nullable|in:pending,active,completed,cancelled',
            'learning_objectives' => 'nullable|string',
        ]);

        $placement->update($validated);

        return redirect()->route('hive.placements.index')->with('success', 'Placement updated.');
    }

    public function destroy(Placement $placement)
    {
        $placement->delete();

        return redirect()->route('hive.placements.index')->with('success', 'Placement deleted.');
    }

    // PDF Generation
    public function generateLetter(Placement $placement)
    {
        $student = $placement->student;
        $data = [
            'office' => config('institution.academic_office').' --- Work Placement',
            'ref' => config('institution.abbreviation').'/WP/'.date('Y').'/'.$placement->id,
            'date' => now(),
            'recipient_title' => 'Mr/Ms',
            'recipient_name' => $placement->organisation_name,
            'recipient_position' => 'HR Manager',
            'recipient_org' => $placement->organisation_name,
            'recipient_city' => 'Maseru',
            'recipient_last_name' => 'Manager',
            'student' => $student,
            'programme' => $placement->programme,
            'year_of_study' => 1,
            'total_years' => 3,
            'placement_start' => $placement->start_date,
            'placement_end' => $placement->end_date,
            'duration' => $placement->duration,
            'proposed_start' => $placement->start_date,
            'placement_type' => $placement->type,
            'industry_sector' => 'culinary / hospitality',
            'coordinator_name' => $this->signatory->get('academic-director'),
        ];

        $pdf = Pdf::loadView('pdf.documents.work_placement', $data);

        return $pdf->stream('Placement_'.$student->name.'.pdf');
    }
}
