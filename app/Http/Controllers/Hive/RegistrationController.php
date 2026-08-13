<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Redirect;

class RegistrationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // If not staff, show student registration page
        if (! $user->isStaff()) {
            $application = Application::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereNotNull('admitted_at')
                ->first();

            if (! $application) {
                return Inertia::render('Hive/Registrations/Index', [
                    'application' => null,
                    'registration' => null,
                ]);
            }

            return Inertia::render('Hive/Registrations/Index', [
                'application' => $application->load(['programme', 'variant']),
                'registration' => [
                    'status' => $application->registration_status,
                    'payment_proof_path' => $application->payment_proof_path,
                    'payment_verified_at' => $application->payment_verified_at,
                    'registered_at' => $application->registered_at,
                    'notes' => $application->registration_notes,
                ],
            ]);
        }

        // Admin index - show all registrations
        $filter = $request->query('filter', 'pending');

        $registrations = Application::with(['user', 'programme'])
            ->whereNotNull('admitted_at')
            ->when($filter !== 'all', fn($q) => $q->where('registration_status', $filter))
            ->latest()
            ->paginate(15);

        $array = $registrations->toArray();

        return Inertia::render('Hive/Registrations/Index', [
            'application' => null,
            'registration' => null,
            'adminList' => [
                'data' => $array['data'],
                'links' => $array['links'],
                'meta' => [
                    'current_page' => $array['current_page'],
                    'from' => $array['from'],
                    'last_page' => $array['last_page'],
                    'path' => $array['path'],
                    'per_page' => $array['per_page'],
                    'to' => $array['to'],
                    'total' => $array['total'],
                ],
            ],
            'filter' => $filter,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $application = Application::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('admitted_at')
            ->firstOrFail();

        abort_unless($application->needsRegistration(), 403, 'Registration is not needed or already submitted.');

        $data = $request->validate([
            'payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB
            // Required profile info
            'date_of_birth' => 'required|date',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:100',
            'dietary_restrictions' => 'nullable|array',
        ]);

        // Store payment proof
        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        // Update user profile with required info
        $user = $request->user();
        $user->profile()->updateOrCreate(
            ['profileable_id' => $user->id, 'profileable_type' => $user->getMorphClass()],
            [
                'date_of_birth' => $data['date_of_birth'],
                'emergency_contact_name' => $data['emergency_contact_name'],
                'emergency_contact_phone' => $data['emergency_contact_phone'],
                'emergency_contact_relationship' => $data['emergency_contact_relationship'],
                'dietary_restrictions' => $data['dietary_restrictions'] ?? [],
            ]
        );

        $application->forceFill([
            'payment_proof_path' => $path,
            'registration_status' => 'submitted',
        ])->save();

        return redirect()->back()->with('success', 'Registration documents submitted. You will be notified once verified.');
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['super-admin', 'it-support', 'registrar', 'program-coordinator']), 403);

        $data = $request->validate([
            'registration_status' => 'required|in:pending,submitted,completed,rejected',
            'registration_notes' => 'sometimes|nullable|string',
        ]);

        $updates = [
            'registration_status' => $data['registration_status'],
            'registration_notes' => $data['registration_notes'] ?? null,
        ];

        if ($data['registration_status'] === 'completed') {
            $updates['registered_at'] = now();
            $updates['payment_verified_at'] = now();

            // Complete registration - assign modules and student role
            $this->completeStudentOnboarding($application);
        }

        $application->forceFill($updates)->save();

        session()->flash('success', 'Registration updated.');

        return Redirect::route('hive.registration.index');
    }

    private function completeStudentOnboarding(Application $application): void
    {
        $student = $application->user;

        if (! $student) {
            $student = \App\Models\User::firstOrCreate(
                ['email' => $application->email],
                [
                    'name' => $application->name,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
                    'email_verified_at' => now(),
                ]
            );
            $application->forceFill(['user_id' => $student->id])->save();
        }

        // Sync modules
        $programme = $application->programme;
        if ($programme && $programme->modules()->exists()) {
            $student->modules()->sync($programme->modules()->pluck('id'));
        }

        // Student role
        if (\Spatie\Permission\Models\Role::where('name', 'student')->exists() && ! $student->hasRole('student')) {
            $student->assignRole('student');
        }

        // Programme
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'programme_id')) {
            $student->forceFill(['programme_id' => $application->programme_id])->save();
        }

        // Student number
        if (empty($student->profile?->student_number)) {
            $student->profile()->updateOrCreate(
                ['profileable_id' => $student->id, 'profileable_type' => $student->getMorphClass()],
                ['student_number' => \App\Services\IdGenerator::generateStudentId($programme?->department_id ?? 0)]
            );
        }
    }

    /**
     * Download proof of registration as PDF.
     */
    public function downloadProof(): \Illuminate\Http\Response
    {
        $user = auth()->user();

        $application = Application::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('registration_status', 'completed')
            ->whereNotNull('registered_at')
            ->first();

        abort_unless($application, 403, 'No completed registration found. Please complete your registration first.');

        $student = $application->user ?? $user;
        $profile = $student->profile;
        $programme = $application->programme;
        $variant = $application->variant;

        $modules = $student->modules()->get();

        $enrolmentDate = \Carbon\Carbon::parse($application->registered_at);
        $durationMonths = $programme->duration_months ?? 6;
        $expectedCompletion = $enrolmentDate->clone()->addMonths($durationMonths);

        $fullName = $student->name;
        $dob = null;

        if ($profile) {
            if ($profile->first_name && $profile->last_name) {
                $fullName = $profile->first_name . ' ' . $profile->last_name;
            }
            if ($profile->date_of_birth) {
                $dob = \Carbon\Carbon::parse($profile->date_of_birth);
            }
        }

        $data = [
            'student' => (object) [
                'full_name' => $fullName,
                'student_number' => $student->student_number ?? 'N/A',
                'dob' => $dob,
                'id_number' => null,
            ],
            'programme' => $programme,
            'year_of_study' => 1,
            'total_years' => $durationMonths >= 12 ? ceil($durationMonths / 12) : 1,
            'academic_year' => $enrolmentDate->format('Y') . '/' . ($enrolmentDate->format('Y') + 1),
            'mode_of_study' => $variant->label ?? 'Full-Time',
            'status' => $application->registration_status,
            'enrolment_date' => $enrolmentDate,
            'expected_completion' => $expectedCompletion,
            'registrar_name' => $this->getSignatory('registrar'),
            'modules' => $modules,
        ];

        $pdf = Pdf::loadView('pdf.documents.proof_of_enrolment', $data);

        return $pdf->download('ProofOfRegistration_' . $student->id . '.pdf');
    }

    private function getSignatory(string $role): string
    {
        $user = \App\Models\User::role($role)->first();

        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }
}
