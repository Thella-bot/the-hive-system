<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Mail\AcceptanceLetter;
use App\Models\Application;
use App\Models\Programme;
use App\Models\User;
use App\Services\IdGenerator;
use App\Services\SignatoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class ApplicationController extends Controller
{
    public function __construct(protected SignatoryService $signatory)
    {
        $this->authorizeResource(Application::class, 'application');
    }

    public function index(Request $request): \Inertia\Response
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');

        $applications = Application::with(['user', 'programme', 'variant'])
            ->when(!$user->isStaff(), fn($q) => $q->where('user_id', $user->id))
            ->when($filter !== 'all', fn($q) => $q->where('status', $filter))
            ->latest()
            ->paginate(12);

        return Inertia::render('Hive/Applications/Index', [
            'applications' => $applications,
            'filter' => $filter,
            'canUpdate' => $user->isAdmin() || $user->hasAnyRole(['registrar', 'program-coordinator', 'admissions-officer']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Hive/Applications/Create', [
            'programmes' => Programme::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'programme_id' => 'required|exists:programmes,id',
            'variant_id' => 'nullable|exists:programme_variants,id',
            'status' => 'nullable|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $request->user()->applications()->create($data);

        return redirect()->route('hive.applications.index')->with('success', 'Application submitted successfully.');
    }

    public function show(Application $application): \Inertia\Response
    {
        $application->load(['user', 'programme', 'variant']);

        return Inertia::render('Hive/Applications/Show', [
            'application' => $application,
            'canUpdate' => request()->user()->can('update', $application),
        ]);
    }

    public function edit(Application $application): \Inertia\Response
    {
        return Inertia::render('Hive/Applications/Edit', [
            'application' => $application->load(['variant']),
            'programmes' => Programme::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'programme_id' => 'nullable|exists:programmes,id',
            'variant_id' => 'nullable|exists:programme_variants,id',
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $wasAdmitted = $application->isAdmitted();
        $becomingAdmitted = $data['status'] === 'approved' && ! $wasAdmitted;
        $wasNoLongerAdmitted = $wasAdmitted && $data['status'] !== 'approved';

        DB::transaction(function () use ($application, $data, $becomingAdmitted, $wasNoLongerAdmitted) {
            $application->update($data);

            if ($becomingAdmitted) {
                $application->forceFill(['admitted_at' => now()])->save();

                $student = $this->ensureStudentAccount($application->fresh(['programme', 'variant', 'user']));
                $this->sendAdmissionEmail($application->fresh(['programme', 'variant', 'user']), $student);
            } elseif ($wasNoLongerAdmitted) {
                $application->forceFill(['admitted_at' => null])->save();

                $student = $application->user;
                if ($student && $student->hasRole('student')) {
                    $student->removeRole('student');
                }
            }
        });

        return redirect()->route('hive.applications.show', $application)
            ->with('success', 'Application updated successfully.');
    }

    // Called by admin to mark registration as complete (after payment proof is verified)
    public function completeRegistration(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['super-admin', 'it-support', 'registrar', 'program-coordinator']), 403);

        $application->forceFill([
            'registration_status' => 'completed',
            'registered_at' => now(),
            'payment_verified_at' => now(),
        ])->save();

        if ($application->user) {
            $student = $application->user;
        } else {
            $student = User::firstOrCreate(
                ['email' => $application->email],
                [
                    'name' => $application->name,
                    'password' => Hash::make(Str::random(12)),
                    'email_verified_at' => now(),
                ]
            );
            $application->forceFill(['user_id' => $student->id])->save();
        }

        $programme = $application->programme ?? $application->load('programme')->programme;
        if ($programme && $programme->modules()->exists()) {
            $student->modules()->sync($programme->modules()->pluck('id'));
        }

        if (Role::where('name', 'student')->exists() && ! $student->hasRole('student')) {
            $student->assignRole('student');
        }

        if (Schema::hasColumn('users', 'programme_id')) {
            $student->forceFill(['programme_id' => $application->programme_id])->save();
        }

        if (Schema::hasColumn('users', 'student_number') && empty($student->student_number)) {
            $student->forceFill(['student_number' => IdGenerator::generateStudentId($programme?->department_id ?? 0)])->save();
        }

        return redirect()->route('hive.applications.show', $application)
            ->with('success', 'Registration completed. Student now has full access.');
    }

    public function destroy(Application $application): RedirectResponse
    {
        $this->authorize('delete', $application);
        $application->delete();

        return redirect()->route('hive.applications.index')
            ->with('success', 'Application deleted successfully.');
    }

    private function ensureStudentAccount(Application $application): User
    {
        $student = $application->user ?: User::firstOrCreate(
            ['email' => $application->email],
            [
                'name' => $application->name,
                'password' => Hash::make(Str::random(12)),
                'email_verified_at' => now(),
            ],
        );

        $updates = [];

        if (! $student->name && $application->name) {
            $updates['name'] = $application->name;
        }

        if (Schema::hasColumn('users', 'programme_id') && ! $student->programme_id) {
            $updates['programme_id'] = $application->programme_id;
        }

        if (empty($student->profile?->student_number)) {
            $student->profile()->create([
                'student_number' => IdGenerator::generateStudentId($application->programme?->department_id ?? 1),
            ]);
        }

        if ($updates) {
            $student->forceFill($updates)->save();
        }

        if (Role::where('name', 'student')->exists() && ! $student->hasRole('student')) {
            $student->assignRole('student');
        }

        if (! $application->user_id) {
            $application->forceFill(['user_id' => $student->id])->save();
        }

        return $student->refresh();
    }

    private function sendAdmissionEmail(Application $application, User $student): void
    {
        $token = Password::createToken($student);
        $passwordResetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $student->getEmailForPasswordReset(),
        ], false));

        Mail::to($student->email)->send(new AcceptanceLetter($application, $student, $passwordResetUrl));
    }

    // ---------- PDF GENERATION ----------

    /**
     * Generate Acceptance Letter PDF.
     */
    public function generateAcceptance(Application $application)
    {
        $user = $application->user;
        $enrollment = $user->enrollments()->where('programme_id', $application->programme_id)->first();

        $data = [
            'office' => config('institution.registrar_office') . ' --- Admissions',
            'ref' => config('institution.abbreviation') . '/ADM/' . date('Y') . '/' . $application->id,
            'date' => now(),
            'student' => $user,
            'programme' => $application->programme,
            'enrollment' => $enrollment ?? $application,
            'intake_date' => $application->admitted_at ?? now(),
            'deadline' => now()->addDays(14),
            'registration_fee' => 500.00,
            'registrar_name' => $this->signatory->get('registrar'),
        ];

        $pdf = Pdf::loadView('pdf.documents.acceptance', $data);
        return $pdf->stream('Acceptance_' . $user->name . '.pdf');
    }

    /**
     * Generate Rejection Letter PDF.
     */
    public function generateRejection(Application $application)
    {
        $user = $application->user;

        $data = [
            'office' => config('institution.registrar_office') . ' --- Admissions',
            'ref' => config('institution.abbreviation') . '/ADM/' . date('Y') . '/' . $application->id,
            'date' => now(),
            'student' => $user,
            'programme' => $application->programme,
            'intake_month' => $application->admitted_at ? $application->admitted_at->format('F Y') : now()->format('F Y'),
            'registrar_name' => $this->signatory->get('registrar'),
            'phone' => '+266 XXXX XXXX',
        ];

        $pdf = Pdf::loadView('pdf.documents.rejection', $data);
        return $pdf->stream('Rejection_' . $user->name . '.pdf');
    }
}
