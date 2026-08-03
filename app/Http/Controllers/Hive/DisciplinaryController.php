<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryAction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DisciplinaryController extends Controller
{
    /**
     * Display a listing of disciplinary actions.
     */
    public function index()
    {
        $actions = DisciplinaryAction::with('user')->paginate(15);
        return inertia('Hive/Disciplinary/Index', ['actions' => $actions]);
    }

    /**
     * Show the form for creating a new disciplinary action.
     */
    public function create()
    {
        $users = User::role(['student', 'staff'])->get();
        return inertia('Hive/Disciplinary/Create', ['users' => $users]);
    }

    /**
     * Store a newly created disciplinary action.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:warning,suspension,expulsion',
            'warning_level' => 'nullable|in:first,second,final',
            'offence' => 'required|string|max:255',
            'incident_description' => 'required|string',
            'hearing_date' => 'required|date',
            'effective_date' => 'required|date',
            'duration' => 'nullable|string',
            'return_date' => 'nullable|date',
            'campus_access' => 'nullable|string',
            'surrender_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'grounds' => 'nullable|array',
            'policy_violated' => 'nullable|string',
            'corrective_actions' => 'nullable|array',
            'advisor_name' => 'nullable|string',
            'hr_rep' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|in:active,expired,appealed',
        ]);

        $action = DisciplinaryAction::create($validated);
        return redirect()->route('disciplinary.index')->with('success', 'Disciplinary action created.');
    }

    /**
     * Show the specified disciplinary action.
     */
    public function show(DisciplinaryAction $disciplinary)
    {
        return inertia('Hive/Disciplinary/Show', ['action' => $disciplinary]);
    }

    /**
     * Show the form for editing.
     */
    public function edit(DisciplinaryAction $disciplinary)
    {
        $users = User::role(['student', 'staff'])->get();
        return inertia('Hive/Disciplinary/Edit', ['action' => $disciplinary, 'users' => $users]);
    }

    /**
     * Update the specified disciplinary action.
     */
    public function update(Request $request, DisciplinaryAction $disciplinary)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:warning,suspension,expulsion',
            'warning_level' => 'nullable|in:first,second,final',
            'offence' => 'required|string|max:255',
            'incident_description' => 'required|string',
            'hearing_date' => 'required|date',
            'effective_date' => 'required|date',
            'duration' => 'nullable|string',
            'return_date' => 'nullable|date',
            'campus_access' => 'nullable|string',
            'surrender_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'grounds' => 'nullable|array',
            'policy_violated' => 'nullable|string',
            'corrective_actions' => 'nullable|array',
            'advisor_name' => 'nullable|string',
            'hr_rep' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|in:active,expired,appealed',
        ]);

        $disciplinary->update($validated);
        return redirect()->route('disciplinary.index')->with('success', 'Disciplinary action updated.');
    }

    /**
     * Remove the specified disciplinary action.
     */
    public function destroy(DisciplinaryAction $disciplinary)
    {
        $disciplinary->delete();
        return redirect()->route('disciplinary.index')->with('success', 'Disciplinary action deleted.');
    }

    // ---------- PDF GENERATION ----------

    /**
     * Generate Warning Letter PDF.
     */
    public function generateWarning(DisciplinaryAction $disciplinary)
    {
        $user = $disciplinary->user;
        $isStudent = $user->hasRole('student');
        $view = $isStudent ? 'pdf.documents.student_warning' : 'pdf.documents.staff_warning';
        $office = $isStudent ? 'Student Affairs --- Disciplinary' : 'Human Resources';

        $data = [
            'office' => $office,
            'ref' => 'HBCI/DSC/' . date('Y') . '/' . $disciplinary->id,
            'date' => now(),
            'student' => $user,
            'programme' => $user->enrollments->first()->programme ?? (object) ['name' => 'N/A'],
            'warning_type' => $disciplinary->warning_level ?? 'First',
            'offence' => $disciplinary->offence,
            'hearing_date' => $disciplinary->hearing_date,
            'incident_description' => $disciplinary->incident_description,
            'rule_violated' => $disciplinary->policy_violated ?? 'Student Code of Conduct',
            'advisor_name' => $disciplinary->advisor_name ?? 'Dean of Students',
            'meeting_deadline' => now()->addDays(3),
            'dean_name' => $isStudent ? $this->getSignatory('dean') : $this->getSignatory('hr-manager'),
            'staff' => $user,
            'policy_violated' => $disciplinary->policy_violated ?? 'HBCI Employment Policy',
            'hr_rep' => $disciplinary->hr_rep ?? 'HR Representative',
            'expiry_date' => $disciplinary->expiry_date ?? now()->addMonths(6),
            'corrective_actions' => $disciplinary->corrective_actions ?? ['Attend training session'],
            'hr_manager_name' => $this->getSignatory('hr-manager'),
        ];

        $pdf = Pdf::loadView($view, $data);
        return $pdf->stream('Warning_' . $user->name . '.pdf');
    }

    /**
     * Generate Suspension Letter PDF.
     */
    public function generateSuspension(DisciplinaryAction $disciplinary)
    {
        $user = $disciplinary->user;
        $data = [
            'office' => 'Student Affairs --- Disciplinary',
            'ref' => 'HBCI/DSC/' . date('Y') . '/' . $disciplinary->id,
            'date' => now(),
            'student' => $user,
            'programme' => $user->enrollments->first()->programme ?? (object) ['name' => 'N/A'],
            'offence' => $disciplinary->offence,
            'hearing_date' => $disciplinary->hearing_date,
            'suspension_type' => $disciplinary->type === 'suspension' ? 'Punitive' : 'Precautionary',
            'effective_date' => $disciplinary->effective_date,
            'duration' => $disciplinary->duration ?? 'Until Further Notice',
            'return_date' => $disciplinary->return_date,
            'campus_access' => $disciplinary->campus_access ?? 'Prohibited',
            'surrender_date' => $disciplinary->surrender_date ?? now()->addDay(),
            'review_date' => $disciplinary->review_date ?? now()->addWeeks(2),
            'director_name' => $this->getSignatory('director'),
        ];

        $pdf = Pdf::loadView('pdf.documents.student_suspension', $data);
        return $pdf->stream('Suspension_' . $user->name . '.pdf');
    }

    /**
     * Generate Expulsion Letter PDF.
     */
    public function generateExpulsion(DisciplinaryAction $disciplinary)
    {
        $user = $disciplinary->user;
        $data = [
            'office' => 'Student Affairs --- Disciplinary',
            'ref' => 'HBCI/DSC/' . date('Y') . '/' . $disciplinary->id,
            'date' => now(),
            'student' => $user,
            'programme' => $user->enrollments->first()->programme ?? (object) ['name' => 'N/A'],
            'hearing_date' => $disciplinary->hearing_date,
            'effective_date' => $disciplinary->effective_date,
            'grounds' => $disciplinary->grounds ?? ['Violation of Code of Conduct'],
            'compliance_deadline' => now()->addDays(5),
            'director_name' => $this->getSignatory('director'),
        ];

        $pdf = Pdf::loadView('pdf.documents.student_expulsion', $data);
        return $pdf->stream('Expulsion_' . $user->name . '.pdf');
    }

    private function getSignatory($role)
    {
        $user = User::role($role)->first();
        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }
}
