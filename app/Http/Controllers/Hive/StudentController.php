<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Actions\Hive\CreateNewStudent;
use App\Actions\Hive\UpdateStudent;
use App\Models\Cohort;
use App\Models\Programme;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $students = User::role(['student', 'parent-guardian', 'alumni'])
            ->with(['profile', 'profile.cohort', 'programme'])
            ->paginate(15);

        return Inertia::render('Hive/Students/Index', [
            'students' => $students,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return Inertia::render('Hive/Students/Create', [
            'programmes' => Programme::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateNewStudent $creator)
    {
        $this->authorize('create', User::class);

        $creator->create($request->all());

        return redirect()->route('hive.students.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $student)
    {
        $this->authorize('view', $student);
        $student->load(['profile', 'programme', 'enrollments.module.programme', 'submissions.gradable']);

        return Inertia::render('Hive/Students/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        $this->authorize('update', $student);
        $isAdmin = auth()->user()?->isAdmin();
        return Inertia::render('Hive/Students/Edit', [
            'managedStudent' => $student->load(['profile', 'programme']),
            'programmes' => Programme::orderBy('name')->get(),
            'cohorts' => Cohort::with('department:id,name')->select('id', 'name', 'department_id')->get(),
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student, UpdateStudent $updater)
    {
        $this->authorize('update', $student);

        $updater->update($student, $request->all());

        return redirect()->route('hive.students.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        $this->authorize('delete', $student);
        $student->delete();
        return redirect()->route('hive.students.index');
    }

    // ---------- PDF GENERATION ----------

    /**
     * Generate Proof of Enrolment PDF.
     */
    public function generateProof(User $student)
    {
        $student->load(['profile', 'enrollments.module.programme']);
        $enrollment = $student->enrollments->first();
        if (!$enrollment) {
            return back()->with('error', 'No enrollment found.');
        }

        $programme = $enrollment->module?->programme ?? $student->programme;
        $profile = $student->profile;

        $data = [
            'office' => 'Registrar',
            'ref' => 'HBCI/REG/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'student' => $student,
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts', 'nqf_level' => 'X', 'duration' => '3 Years'],
            'year_of_study' => 1,
            'total_years' => $programme->duration ?? 3,
            'academic_year' => date('Y') . '/' . (date('Y') + 1),
            'mode_of_study' => 'Full-Time',
            'status' => 'ACTIVE',
            'enrolment_date' => $profile->enrollment_date ?? $student->created_at,
            'expected_completion' => now()->addYears($programme->duration ?? 3),
            'registrar_name' => $this->getSignatory('registrar'),
        ];

        $pdf = Pdf::loadView('pdf.documents.proof_of_enrolment', $data);
        return $pdf->stream('Proof_of_Enrolment_' . $student->name . '.pdf');
    }

    /**
     * Generate Certificate of Completion PDF.
     */
    public function generateCertificate(User $student)
    {
        $student->load(['profile', 'enrollments.module.programme']);
        $enrollment = $student->enrollments->first();
        $programme = $enrollment?->module?->programme ?? $student->programme;

        if (!$enrollment) {
            return back()->with('error', 'No enrollment found.');
        }

        $data = [
            'office' => 'Registrar',
            'ref' => 'HBCI/REG/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'student' => $student,
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts', 'nqf_level' => 'X', 'duration' => '3 Years'],
            'award' => 'Merit',
            'director_name' => $this->getSignatory('super-admin'),
            'registrar_name' => $this->getSignatory('registrar'),
            'issue_date' => now(),
            'certificate_number' => 'HBCI-CERT-' . date('Y') . '-' . $student->id,
        ];

        $pdf = Pdf::loadView('pdf.documents.certificate', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream('Certificate_' . $student->name . '.pdf');
    }

    /**
     * Generate Reference Letter PDF.
     */
    public function generateReference(User $student, Request $request)
    {
        $student->load(['profile', 'programme']);
        $programme = $student->programme;

        $data = [
            'office' => 'Academic Office',
            'ref' => 'HBCI/REF/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'recipient_title' => $request->recipient_title ?? 'Dr',
            'recipient_name' => $request->recipient_name ?? 'John Doe',
            'recipient_position' => $request->recipient_position ?? 'Admissions Officer',
            'recipient_org' => $request->recipient_org ?? 'University of Example',
            'recipient_city' => $request->recipient_city ?? 'Maseru',
            'recipient_last_name' => $request->recipient_last_name ?? 'Doe',
            'student' => $student,
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts'],
            'application_for' => $request->application_for ?? 'the position of Sous Chef',
            'relationship' => $request->relationship ?? 'Programme Coordinator',
            'period_known' => $request->period_known ?? '2 years',
            'start_year' => $request->start_year ?? '2023',
            'completion_status' => $request->completion_status ?? 'has successfully completed',
            'grade_summary' => $request->grade_summary ?? 'commendable results',
            'gpa_record' => $request->gpa_record ?? '3.8 GPA',
            'academic_achievements' => $request->academic_achievements ?? 'Excelled in pastry and kitchen management modules.',
            'character_traits' => $request->character_traits ?? 'hardworking and innovative',
            'character_examples' => $request->character_examples ?? 'took initiative in organising a charity dinner',
            'character_details' => $request->character_details ?? 'Showed excellent leadership and teamwork skills.',
            'industry_readiness' => $request->industry_readiness ?? 'Ready to work in a fast-paced professional kitchen.',
            'referee_name' => $this->getSignatory('super-admin'),
            'referee_title' => 'Senior Lecturer',
            'referee_phone' => '+266 XXXX XXXX',
            'referee_email' => 'lecturer@hbci.ac.ls',
        ];

        $pdf = Pdf::loadView('pdf.documents.reference', $data);
        return $pdf->stream('Reference_' . $student->name . '.pdf');
    }

    private function getSignatory($role)
    {
        $user = User::role($role)->first();
        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }
}
