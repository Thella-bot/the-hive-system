<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Actions\Hive\CreateNewStudent;
use App\Actions\Hive\UpdateStudent;
use App\Http\Controllers\Concerns\GeneratesDocumentPdfs;
use App\Models\Cohort;
use App\Models\Programme;
use App\Models\User;
use App\Services\ReferenceDataService;
use App\Services\SignatoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;

class StudentController extends Controller
{
    use GeneratesDocumentPdfs;
    public function __construct(protected SignatoryService $signatory) {}

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
            'programmes' => app(ReferenceDataService::class)->programmes(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateNewStudent $creator)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8|confirmed',
            'student_number' => 'nullable|string',
            'programme_id' => 'nullable|exists:programmes,id',
        ]);

        $creator->create($validated);

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
            'programmes' => app(ReferenceDataService::class)->programmes(),
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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->id,
            'password' => 'nullable|string|min:8|confirmed',
            'student_number' => 'nullable|string',
            'programme_id' => 'nullable|exists:programmes,id',
            'cohort_id' => 'nullable|exists:cohorts,id',
            'enrollment_date' => 'nullable|date',
            'expected_graduation_date' => 'nullable|date',
            'status' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:255',
        ]);

        $updater->update($student, $validated, $request->user()->isAdmin());

        return redirect()->route('hive.students.index')
            ->with('success', 'Student updated successfully.');
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

        $fullName = $student->name;
        if ($profile && $profile->first_name && $profile->last_name) {
            $fullName = $profile->first_name . ' ' . $profile->last_name;
        }

        $dob = $profile?->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth) : null;

        $data = [
            'office' => config('institution.registrar_office'),
            'ref' => config('institution.abbreviation') . '/REG/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'student' => (object) [
                'full_name' => $fullName,
                'student_number' => $student->student_number ?? ($profile?->student_number ?? 'N/A'),
                'dob' => $dob,
                'id_number' => $student->national_id_number ?? null,
            ],
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts', 'nqf_level' => 'X', 'duration' => '3 Years'],
            'year_of_study' => 1,
            'total_years' => $programme->duration ?? 3,
            'academic_year' => date('Y') . '/' . (date('Y') + 1),
            'mode_of_study' => 'Full-Time',
            'status' => 'ACTIVE',
            'enrolment_date' => $profile->enrollment_date ?? $student->created_at,
            'expected_completion' => now()->addYears($programme->duration ?? 3),
            'registrar_name' => $this->signatory->get('registrar'),
            'modules' => $student->modules()->get(),
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
            'office' => config('institution.registrar_office'),
            'ref' => config('institution.abbreviation') . '/REG/' . date('Y') . '/' . $student->id,
            'date' => now(),
            'student' => $student,
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts', 'nqf_level' => 'X', 'duration' => '3 Years'],
            'award' => 'Merit',
            'director_name' => $this->signatory->get('super-admin'),
            'registrar_name' => $this->signatory->get('registrar'),
            'issue_date' => now(),
            'certificate_number' => config('institution.abbreviation') . '-CERT-' . date('Y') . '-' . $student->id,
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
            'office' => config('institution.academic_office'),
            'ref' => config('institution.abbreviation') . '/REF/' . date('Y') . '/' . $student->id,
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
            'referee_name' => $this->signatory->get('super-admin'),
            'referee_title' => 'Senior Lecturer',
            'referee_phone' => '+266 XXXX XXXX',
            'referee_email' => 'lecturer@hbci.ac.ls',
        ];

        $pdf = Pdf::loadView('pdf.documents.reference', $data);
        return $pdf->stream('Reference_' . $student->name . '.pdf');
    }
}
