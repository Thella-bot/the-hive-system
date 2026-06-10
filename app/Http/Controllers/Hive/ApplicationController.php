<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Mail\AcceptanceLetter;
use App\Models\Application;
use App\Models\Programme;
use App\Models\User;
use App\Services\IdGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class ApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Application::class, 'application');
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $filter = $request->query('filter', 'pending');

        $paginatedApplications = Application::with(['user', 'programme', 'variant'])
            ->when($filter !== 'all', fn($q) => $q->where('status', $filter))
            ->when(! $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']), fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->paginate(12);

        $applicationsArray = $paginatedApplications->toArray();

        return Inertia::render('Hive/Applications/Index', [
            'applications' => [
                'data' => $applicationsArray['data'],
                'links' => $applicationsArray['links'],
                'meta' => [
                    'current_page' => $applicationsArray['current_page'],
                    'from' => $applicationsArray['from'],
                    'last_page' => $applicationsArray['last_page'],
                    'path' => $applicationsArray['path'],
                    'per_page' => $applicationsArray['per_page'],
                    'to' => $applicationsArray['to'],
                    'total' => $applicationsArray['total'],
                ],
            ],
            'canUpdate' => $user->hasAnyRole(['super-admin', 'school-admin', 'non_academic_staff']),
            'filter' => $filter,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Hive/Applications/Create', [
            'programmes' => Programme::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
        ]);

        $request->user()->applications()->create($data);

        return redirect()->route('hive.applications.index')->with('success', 'Application submitted successfully.');
    }

    public function show(Application $application): Response
    {
        $application->load(['user', 'programme', 'variant']);

        return Inertia::render('Hive/Applications/Show', [
            'application' => $application,
            'canUpdate' => request()->user()->can('update', $application),
        ]);
    }

    public function edit(Application $application): Response
    {
        return $this->show($application);
    }

    public function destroy(Application $application): RedirectResponse
    {
        $application->delete();

        return redirect()->route('hive.applications.index')->with('success', 'Application deleted successfully.');
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $wasAdmitted = $application->isAdmitted();
        $becomingAdmitted = $data['status'] === 'approved' && ! $wasAdmitted;
        $wasNoLongerAdmitted = $wasAdmitted && $data['status'] !== 'approved';

        DB::transaction(function () use ($application, $data, $becomingAdmitted, $wasNoLongerAdmitted) {
            $application->update($data);

            if ($becomingAdmitted) {
                // Set admitted_at timestamp
                $application->forceFill(['admitted_at' => now()])->save();

                // Create user account (but no modules yet - that's after registration)
                $student = $this->ensureStudentAccount($application->fresh(['programme', 'variant', 'user']));
                $this->sendAdmissionEmail($application->fresh(['programme', 'variant', 'user']), $student);
            } elseif ($wasNoLongerAdmitted) {
                // Revoke admitted status if status changes away from approved
                $application->forceFill(['admitted_at' => null])->save();

                // Revoke the student role so they lose access to student-only routes
                $student = $application->user;
                if ($student && $student->hasRole('student')) {
                    $student->removeRole('student');
                }
            }
        });

        return redirect()->route('hive.applications.show', $application)->with('success', 'Application updated successfully.');
    }

    // Called by admin to mark registration as complete (after payment proof is verified)
    public function completeRegistration(Request $request, Application $application): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['super-admin', 'school-admin', 'non_academic_staff']), 403);

        $application->forceFill([
            'registration_status' => 'completed',
            'registered_at' => now(),
            'payment_verified_at' => now(),
        ])->save();

        // Now assign modules and create student account (if not already created)
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
            // If the user already existed (firstOrCreate matched), do NOT overwrite their password
            $application->forceFill(['user_id' => $student->id])->save();
        }

        // Sync modules from programme (load relation first)
        $programme = $application->programme ?? $application->load('programme')->programme;
        if ($programme && $programme->modules()->exists()) {
            $student->modules()->sync($programme->modules()->pluck('id'));
        }

        // Ensure student role
        if (Role::where('name', 'student')->exists() && ! $student->hasRole('student')) {
            $student->assignRole('student');
        }

        // Assign programme
        if (Schema::hasColumn('users', 'programme_id')) {
            $student->forceFill(['programme_id' => $application->programme_id])->save();
        }

        // Generate student number if applicable
        if (Schema::hasColumn('users', 'student_number') && empty($student->student_number)) {
            $student->forceFill(['student_number' => IdGenerator::generateStudentId($programme?->department_id ?? 0)])->save();
        }

        return redirect()->route('hive.applications.show', $application)
            ->with('success', 'Registration completed. Student now has full access.');
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

        // Set programme (but DO NOT sync modules - that's done after registration is completed)
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

        // Assign student role (so they can log in and see registration page)
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

}