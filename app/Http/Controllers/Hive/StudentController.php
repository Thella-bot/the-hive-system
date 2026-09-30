<?php

declare(strict_types=1);

namespace App\Http\Controllers\Hive;

use App\Actions\Hive\CreateNewStudent;
use App\Actions\Hive\UpdateStudent;
use App\Http\Controllers\Concerns\GeneratesDocumentPdfs;
use App\Http\Controllers\Controller;
use App\Mail\StudentWelcomeEmail;
use App\Models\Cohort;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ReferenceDataService;
use App\Services\SignatoryService;
use App\Services\StudentExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StudentController extends Controller
{
    use GeneratesDocumentPdfs;

    public function __construct(
        protected SignatoryService $signatory,
        protected AuditService $audit,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, StudentExportService $exports)
    {
        $this->authorize('viewAny', User::class);

        $filters = $this->validatedFilters($request);
        $options = $exports->filterOptions();

        $students = $exports->query($filters)->paginate(15)->withQueryString();

        return Inertia::render('Hive/Students/Index', [
            'students' => $students,
            'filters' => $filters,
            'canExport' => $request->user()?->canExportStudents() ?? false,
            'exportUrl' => route('hive.students.export'),
            'filterOptions' => [
                'programmes' => $options['programmes'],
                'cohorts' => $options['cohorts'],
                'departments' => $options['departments'],
                'statuses' => $exports->statuses(),
            ],
        ]);
    }

    /**
     * Export the student register to CSV.
     *
     * Honours the same filters as the listing, so the file contains exactly the
     * rows the administrator was looking at.
     */
    public function export(Request $request, StudentExportService $exports)
    {
        $this->authorize('viewAny', User::class);

        abort_unless($request->user()?->canExportStudents(), 403, 'You are not allowed to export the student register.');

        $filters = $this->validatedFilters($request);

        $columns = $this->validatedColumns($request, $exports);
        $definitions = $exports->columns();
        $headers = array_map(fn (string $column) => $definitions[$column]['label'], $columns);

        $rows = $exports->rows($exports->query($filters)->get(), $columns);

        $filename = 'student_register_'.now()->format('Y-m-d_His').'.csv';

        // The register holds contact details and national ID numbers, so every
        // download is recorded.
        $this->audit->log('exported', $request->user(), [
            'resource' => 'student-register',
            'columns' => $columns,
            'filters' => $filters,
            'row_count' => $rows->count(),
        ]);

        return Response::streamDownload(function () use ($rows, $headers) {
            $file = fopen('php://output', 'w');

            // Byte order mark, so Excel opens UTF-8 names correctly.
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, array_values($row));
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    /**
     * The filters shared by the listing and the export.
     *
     * @return array<string, mixed>
     */
    protected function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::in(['active', 'graduated', 'on_leave', 'suspended', 'withdrawn'])],
            'programme_id' => ['nullable', 'integer', 'exists:programmes,id'],
            'cohort_id' => ['nullable', 'integer', 'exists:cohorts,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        // Drop empties so the export URL stays clean and cache-friendly.
        return array_filter($validated, fn ($value) => $value !== null && $value !== '');
    }

    /**
     * The requested columns, falling back to the full register.
     *
     * @return array<int, string>
     */
    protected function validatedColumns(Request $request, StudentExportService $exports): array
    {
        $available = $exports->defaultColumns();

        $requested = $request->input('columns');

        if (is_string($requested)) {
            $requested = array_filter(explode(',', $requested));
        }

        if (! is_array($requested) || $requested === []) {
            return $available;
        }

        $selected = array_values(array_intersect($available, $requested));

        return $selected === [] ? $available : $selected;
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
            'password_confirmation' => 'nullable|string',
            'student_number' => 'nullable|string|unique:profiles,student_number',
            'programme_id' => 'nullable|exists:programmes,id',
        ]);

        // Sanitize inputs
        $validated['name'] = strip_tags($validated['name']);
        $validated['email'] = filter_var($validated['email'], FILTER_SANITIZE_EMAIL);

        // Capture plain password for welcome email (if provided)
        $plainPassword = $validated['password'] ?? null;

        $student = $creator->create($validated);

        // Send welcome email
        try {
            Mail::to($student->email)->send(
                new StudentWelcomeEmail($student, $plainPassword)
            );
        } catch (\Exception $e) {
            // Log error but don't prevent student creation
            Log::warning('Failed to send welcome email: '.$e->getMessage());
        }

        // Log audit trail
        $this->audit->logCreated($student);

        return redirect()->route('hive.students.index')
            ->with('success', 'Student created successfully.');
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
        $canEditAllFields = auth()->user()?->canManageStudents() ?? false;

        return Inertia::render('Hive/Students/Edit', [
            'managedStudent' => $student->load(['profile', 'programme']),
            'programmes' => app(ReferenceDataService::class)->programmes(),
            'cohorts' => Cohort::with('department:id,name')->select('id', 'name', 'department_id')->get(),
            'isAdmin' => $canEditAllFields,
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
            'email' => 'required|email|unique:users,email,'.$student->id,
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string',
            'student_number' => 'nullable|string|unique:profiles,student_number,'.($student->profile?->id ?? 'NULL').',id',
            'programme_id' => 'nullable|exists:programmes,id',
            'cohort_id' => 'nullable|exists:cohorts,id',
            'enrollment_date' => 'nullable|date',
            'expected_graduation_date' => 'nullable|date',
            'status' => 'nullable|string',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'national_id_number' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:255',
        ]);

        // Sanitize inputs
        $validated['name'] = strip_tags($validated['name']);
        $validated['email'] = filter_var($validated['email'], FILTER_SANITIZE_EMAIL);

        // Capture old values for audit
        $oldValues = $student->getAttributes();
        if ($student->profile) {
            $oldValues['profile'] = $student->profile->getAttributes();
        }

        $updater->update($student, $validated, $request->user()->canManageStudents());

        $transfer = $updater->transferSummary();

        // Log audit trail
        $student->refresh();
        $this->audit->logUpdated($student, $oldValues);

        $message = 'Student updated successfully.';

        if ($transfer && $transfer['student_number_from'] !== $transfer['student_number_to']) {
            $message .= $transfer['department_changed']
                ? sprintf(
                    ' Student number changed from %s to %s because the programme belongs to a different department.',
                    $transfer['student_number_from'] ?? 'none',
                    $transfer['student_number_to'] ?? 'none',
                )
                : sprintf(
                    ' Student number changed from %s to %s.',
                    $transfer['student_number_from'] ?? 'none',
                    $transfer['student_number_to'] ?? 'none',
                );
        }

        return redirect()->route('hive.students.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        $this->authorize('delete', $student);

        // Log audit trail before deletion
        $this->audit->logDeleted($student);

        $student->delete();

        return redirect()->route('hive.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    // ---------- PDF GENERATION ----------

    /**
     * Generate Proof of Enrolment PDF.
     */
    public function generateProof(User $student)
    {
        $this->authorize('generateProof', $student);
        $student->load(['profile', 'enrollments.module.programme', 'modules']);
        $enrollment = $student->enrollments->first();
        if (! $enrollment) {
            return back()->with('error', 'No enrollment found.');
        }

        $programme = $enrollment->module?->programme ?? $student->programme;
        $profile = $student->profile;

        $fullName = $student->name;
        if ($profile && $profile->first_name && $profile->last_name) {
            $fullName = $profile->first_name.' '.$profile->last_name;
        }

        $dob = $profile?->date_of_birth ? Carbon::parse($profile->date_of_birth) : null;

        $data = [
            'office' => config('institution.registrar_office'),
            'ref' => config('institution.abbreviation').'/REG/'.date('Y').'/'.$student->id,
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
            'academic_year' => date('Y').'/'.(date('Y') + 1),
            'mode_of_study' => 'Full-Time',
            'status' => 'ACTIVE',
            'enrolment_date' => $profile->enrollment_date ?? $student->created_at,
            'expected_completion' => now()->addYears($programme->duration ?? 3),
            'registrar_name' => $this->signatory->get('registrar'),
            'modules' => $student->modules,
        ];

        $pdf = Pdf::loadView('pdf.documents.proof_of_enrolment', $data);

        return $pdf->stream('Proof_of_Enrolment_'.$student->name.'.pdf');
    }

    /**
     * Generate Certificate of Completion PDF.
     */
    public function generateCertificate(User $student)
    {
        $this->authorize('generateCertificate', $student);
        $student->load(['profile', 'enrollments.module.programme']);
        $enrollment = $student->enrollments->first();
        $programme = $enrollment?->module?->programme ?? $student->programme;

        if (! $enrollment) {
            return back()->with('error', 'No enrollment found.');
        }

        $data = [
            'office' => config('institution.registrar_office'),
            'ref' => config('institution.abbreviation').'/REG/'.date('Y').'/'.$student->id,
            'date' => now(),
            'student' => $student,
            'programme' => $programme ?? (object) ['name' => 'Culinary Arts', 'nqf_level' => 'X', 'duration' => '3 Years'],
            'award' => 'Merit',
            'director_name' => $this->signatory->get('super-admin'),
            'registrar_name' => $this->signatory->get('registrar'),
            'issue_date' => now(),
            'certificate_number' => config('institution.abbreviation').'-CERT-'.date('Y').'-'.$student->id,
        ];

        $pdf = Pdf::loadView('pdf.documents.certificate', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('Certificate_'.$student->name.'.pdf');
    }

    /**
     * Generate Reference Letter PDF.
     */
    public function generateReference(User $student, Request $request)
    {
        $this->authorize('generateReference', $student);
        $student->load(['profile', 'programme']);
        $programme = $student->programme;

        $data = [
            'office' => config('institution.academic_office'),
            'ref' => config('institution.abbreviation').'/REF/'.date('Y').'/'.$student->id,
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

        return $pdf->stream('Reference_'.$student->name.'.pdf');
    }
}
