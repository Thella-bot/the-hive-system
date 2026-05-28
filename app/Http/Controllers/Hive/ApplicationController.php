<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Mail\AcceptanceLetter;
use App\Models\Application;
use App\Models\Programme;
use App\Models\User;
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

        $applications = Application::with(['user', 'programme', 'variant'])
            ->when(! $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff', 'non_academic_staff']), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->paginate(12);

        return Inertia::render('Hive/Applications/Index', [
            'applications' => $applications,
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

        $wasApproved = $application->status === 'approved';

        DB::transaction(function () use ($application, $data, $wasApproved) {
            $application->update($data);

            if ($data['status'] === 'approved' && ! $wasApproved) {
                $student = $this->ensureStudentAccount($application->fresh(['programme', 'variant', 'user']));
                $this->sendAcceptanceWelcomePack($application->fresh(['programme', 'variant', 'user']), $student);
            }
        });

        return redirect()->route('hive.applications.show', $application)->with('success', 'Application updated successfully.');
    }

    private function ensureStudentAccount(Application $application): User
    {
        $student = $application->user ?: User::firstOrCreate(
            ['email' => $application->email],
            [
                'name' => $application->name,
                'password' => Hash::make(Str::password(32)),
                'email_verified_at' => now(),
            ],
        );

        $updates = [];

        if (! $student->name && $application->name) {
            $updates['name'] = $application->name;
        }

        $needsModuleSync = false;

        if (Schema::hasColumn('users', 'programme_id') && ! $student->programme_id) {
            $updates['programme_id'] = $application->programme_id;
            $needsModuleSync = true;
        }

        if (Schema::hasColumn('users', 'student_number') && empty($student->student_number)) {
            $updates['student_number'] = $this->generateStudentNumber();
        }

        if ($updates) {
            $student->forceFill($updates)->save();
        }

        if ($needsModuleSync) {
            $programme = Programme::find($application->programme_id);
            if ($programme && $programme->modules()->exists()) {
                $student->modules()->sync($programme->modules()->pluck('id'));
            }
        }

        if (Role::where('name', 'student')->exists() && ! $student->hasRole('student')) {
            $student->assignRole('student');
        }

        if (! $application->user_id) {
            $application->forceFill(['user_id' => $student->id])->save();
        }

        return $student->refresh();
    }

    private function sendAcceptanceWelcomePack(Application $application, User $student): void
    {
        $token = Password::createToken($student);
        $passwordResetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $student->getEmailForPasswordReset(),
        ], false));

        Mail::to($student->email)->send(new AcceptanceLetter($application, $student, $passwordResetUrl));
    }

    private function generateStudentNumber(): string
    {
        $prefix = 'HBCI' . now()->format('Y');
        $lastNumber = User::query()
            ->where('student_number', 'like', $prefix . '%')
            ->max('student_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
