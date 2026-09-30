<?php

declare(strict_types=1);

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\EnrollmentRequest;
use App\Models\Module;
use App\Models\User;
use App\Notifications\EnrollmentRequestDecided;
use App\Notifications\EnrollmentRequestSubmitted;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * Admin enrollment dashboard.
     */
    public function index(Request $request): Response
    {
        $this->authorize('create', Enrollment::class);

        $user = $request->user();
        $query = Enrollment::with(['student', 'module']);

        if ($request->filled('module_id')) {
            $query->forModule($request->input('module_id'));
        }

        if ($request->filled('academic_year')) {
            $query->forAcademicYear($request->input('academic_year'));
        } else {
            $currentYear = AcademicYear::current()->first();
            if ($currentYear) {
                $query->forAcademicYear($currentYear->name);
            }
        }

        if ($request->filled('semester')) {
            $query->forSemester((int) $request->input('semester'));
        }

        $enrollments = $query->orderByDesc('created_at')->get();

        // Group by module so staff see a class list per module instead of one
        // flat row per student-module pair.
        $grouped = $enrollments
            ->groupBy('module_id')
            ->map(function ($rows, $moduleId) {
                $first = $rows->first();
                $module = $first?->module;

                return [
                    'module_id' => (int) $moduleId,
                    'code' => $module?->code,
                    'name' => $module?->name,
                    'count' => $rows->count(),
                    'students' => $rows->map(fn ($e) => [
                        'enrollment_id' => $e->id,
                        'student_id' => $e->user_id,
                        'name' => $e->student?->name,
                        'student_number' => $e->student?->student_number,
                        'academic_year' => $e->academic_year,
                        'semester' => (string) $e->semester,
                    ])->values(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        return Inertia::render('Enrollment/AdminIndex', [
            'enrollmentGroups' => $grouped,
            'totalEnrollments' => $enrollments->count(),
            'moduleCount' => $grouped->count(),
            'modules' => Module::orderBy('name')->get(['id', 'name', 'code']),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'filters' => $request->only('module_id', 'academic_year', 'semester'),
            'pendingRequestCount' => EnrollmentRequest::pending()->count(),
        ]);
    }

    /**
     * Show enrollment form for a specific student.
     */
    public function enrollStudent(Request $request, User $student): Response
    {
        $this->authorize('create', Enrollment::class);

        $currentYear = AcademicYear::current()->first();
        $semester = now()->month <= 6 ? '1' : '2';

        $enrolledModuleIds = Enrollment::query()
            ->where('user_id', $student->id)
            ->where('academic_year', $currentYear?->name ?? date('Y'))
            ->where('semester', $semester)
            ->pluck('module_id')
            ->toArray();

        $programme = $student->programme;
        $yearLevel = $student->getCurrentSemesterContext()['year_level'] ?? 1;

        $availableModules = collect();
        if ($programme) {
            $availableModules = Module::whereHas('programmes', function ($q) use ($programme, $yearLevel, $semester) {
                $q->where('programme_module.programme_id', $programme->id)
                    ->where('programme_module.year_level', $yearLevel)
                    ->where('programme_module.semester', $semester);
            })->whereNotIn('id', $enrolledModuleIds)->orderBy('name')->get();
        }

        return Inertia::render('Enrollment/EnrollStudent', [
            'student' => $student->load('profile'),
            'programme' => $programme,
            'yearLevel' => $yearLevel,
            'semester' => $semester,
            'academicYear' => $currentYear,
            'enrolledModuleIds' => $enrolledModuleIds,
            'availableModules' => $availableModules,
        ]);
    }

    /**
     * Student-facing module list (enroll/drop own modules).
     */
    public function studentIndex(Request $request): Response
    {
        $user = $request->user();
        $this->authorize('viewStudent', Enrollment::class);

        $currentYear = AcademicYear::current()->first();
        $semester = now()->month <= 6 ? '1' : '2';
        $currentYearName = $currentYear?->name ?? date('Y');

        $context = $user->getCurrentSemesterContext();
        $yearLevel = $context['year_level'] ?? 1;
        $cohortYear = $user->profile?->cohort?->academicYear?->name;
        $isRepeatingYear = $cohortYear !== null
            && $cohortYear !== ''
            && (int) $currentYearName > (int) $cohortYear;

        $enrolledModuleIds = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('academic_year', $currentYearName)
            ->where('semester', $semester)
            ->pluck('module_id')
            ->toArray();

        $programme = $user->programme;
        $availableModules = collect();

        if ($programme) {
            $currentYearIds = Module::whereHas('programmes', function ($q) use ($programme, $yearLevel, $semester) {
                $q->where('programme_module.programme_id', $programme->id)
                    ->where('programme_module.year_level', $yearLevel)
                    ->where('programme_module.semester', $semester);
            })->pluck('modules.id');

            $moduleIds = $currentYearIds;

            if ($isRepeatingYear) {
                $allSemesterIds = Module::whereHas('programmes', function ($q) use ($programme, $semester) {
                    $q->where('programme_module.programme_id', $programme->id)
                        ->where('programme_module.semester', $semester);
                })->pluck('modules.id');

                $moduleIds = $currentYearIds->merge($allSemesterIds)->unique();
            }

            $availableModules = Module::whereIn('modules.id', $moduleIds)
                ->whereNotIn('modules.id', $enrolledModuleIds)
                ->orderBy('name')
                ->with('department:id,name')
                ->get();
        }

        $enrolledModules = Enrollment::where('user_id', $user->id)
            ->with('module:id,name,code')
            ->get()
            ->map(fn ($e) => [
                'enrollment_id' => $e->id,
                'module_id' => $e->module_id,
                'academic_year' => $e->academic_year,
                'semester' => (string) $e->semester,
                'code' => $e->module?->code,
                'name' => $e->module?->name,
            ]);

        $pendingRequests = EnrollmentRequest::where('user_id', $user->id)
            ->where('status', EnrollmentRequest::STATUS_PENDING)
            ->with('module:id,name,code')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'module_id' => $r->module_id,
                'type' => $r->type,
                'module' => $r->module?->name,
            ]);

        return Inertia::render('Enrollment/Index', [
            'modules' => $availableModules,
            'enrolledModuleIds' => $enrolledModuleIds,
            'enrolledModules' => $enrolledModules,
            'pendingRequests' => $pendingRequests,
            'semesterContext' => [
                'year_level' => $yearLevel,
                'semester' => $semester,
            ],
            'isRepeatingYear' => $isRepeatingYear,
        ]);
    }

    /**
     * Submit a request to enroll in a module. Creates a pending request only;
     * an authorised reviewer must approve it before an Enrollment exists.
     */
    public function studentStore(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('viewStudent', Enrollment::class);

        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $currentYear = AcademicYear::current()->first();
        $currentYearName = $currentYear?->name ?? date('Y');
        $context = $user->getCurrentSemesterContext();
        $semester = $context['semester'] ?? (now()->month <= 6 ? '1' : '2');
        $yearLevel = $context['year_level'] ?? 1;

        $module = Module::findOrFail($data['module_id']);
        $programme = $user->programme;

        if ($programme) {
            $isAllowed = $module->programmes()
                ->where('programme_module.programme_id', $programme->id)
                ->where('programme_module.year_level', $yearLevel)
                ->where('programme_module.semester', $semester)
                ->exists();

            if (! $isAllowed) {
                $isRepeatingYear = (int) $currentYearName > (int) ($user->profile?->cohort?->academicYear?->name ?? $currentYearName);

                if (! $isRepeatingYear) {
                    return back()->with('error', 'You cannot request a module outside your current semester.');
                }
            }
        }

        $exists = Enrollment::where('user_id', $user->id)
            ->where('module_id', $data['module_id'])
            ->where('academic_year', $currentYearName)
            ->where('semester', $semester)
            ->exists();

        if ($exists) {
            return back()->with('info', 'You are already enrolled in this module.');
        }

        $pending = EnrollmentRequest::where('user_id', $user->id)
            ->where('module_id', $data['module_id'])
            ->where('type', EnrollmentRequest::TYPE_ENROLLMENT)
            ->where('status', EnrollmentRequest::STATUS_PENDING)
            ->exists();

        if ($pending) {
            return back()->with('info', 'You already have a pending request for this module.');
        }

        $enrollmentRequest = EnrollmentRequest::create([
            'user_id' => $user->id,
            'module_id' => $data['module_id'],
            'type' => EnrollmentRequest::TYPE_ENROLLMENT,
            'academic_year' => $currentYearName,
            'semester' => $semester,
            'reason' => $data['reason'] ?? null,
        ]);

        $this->audit->logCreated($enrollmentRequest);
        $this->notifyReviewers($enrollmentRequest);

        return back()->with('success', 'Enrollment request submitted for approval.');
    }

    /**
     * Submit a request to drop a module. Creates a pending request only; the
     * enrollment is removed once a reviewer approves.
     */
    public function studentDestroy(Request $request, Module $module): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('viewStudent', Enrollment::class);

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        if (! $enrollment) {
            return back()->with('info', 'You are not enrolled in this module.');
        }

        $pending = EnrollmentRequest::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->where('type', EnrollmentRequest::TYPE_DEREGISTRATION)
            ->where('status', EnrollmentRequest::STATUS_PENDING)
            ->exists();

        if ($pending) {
            return back()->with('info', 'You already have a pending deregistration request for this module.');
        }

        $enrollmentRequest = EnrollmentRequest::create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'type' => EnrollmentRequest::TYPE_DEREGISTRATION,
            'academic_year' => $enrollment->academic_year,
            'semester' => (string) $enrollment->semester,
        ]);

        $this->audit->logCreated($enrollmentRequest);
        $this->notifyReviewers($enrollmentRequest);

        return back()->with('success', 'Deregistration request submitted for approval.');
    }

    /**
     * Enroll a student in a module (admin only).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Enrollment::class);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'module_id' => 'required|exists:modules,id',
            'academic_year' => 'required|string',
            'semester' => 'required|integer|in:1,2',
        ]);

        $exists = Enrollment::where('user_id', $data['user_id'])
            ->where('module_id', $data['module_id'])
            ->where('academic_year', $data['academic_year'])
            ->where('semester', $data['semester'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Student is already enrolled in this module.');
        }

        $enrollment = Enrollment::create($data);

        $user = User::find($data['user_id']);
        if ($user) {
            $user->modules()->syncWithoutDetaching([$data['module_id']]);
        }

        $this->audit->logCreated($enrollment);

        return back()->with('success', 'Student enrolled successfully.');
    }

    /**
     * Remove a student from a module (admin only).
     */
    public function destroy(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $this->authorize('create', Enrollment::class);

        $this->audit->logDeleted($enrollment);

        $enrollment->delete();

        $user = User::find($enrollment->user_id);
        if ($user) {
            $user->modules()->detach($enrollment->module_id);
        }

        return back()->with('success', 'Student removed from module.');
    }

    /**
     * Update an enrollment (admin only).
     */
    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $this->authorize('create', Enrollment::class);

        $data = $request->validate([
            'academic_year' => 'sometimes|string',
            'semester' => 'sometimes|integer|in:1,2',
        ]);

        $this->audit->logUpdated($enrollment, $enrollment->getAttributes());

        $enrollment->update($data);

        return back()->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Bulk enroll students into a module (admin only).
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $this->authorize('create', Enrollment::class);

        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'academic_year' => 'required|string',
            'semester' => 'required|integer|in:1,2',
        ]);

        $module = Module::findOrFail($data['module_id']);
        $academicYear = $data['academic_year'];
        $semester = $data['semester'];

        $enrolled = 0;
        $skipped = 0;

        DB::transaction(function () use ($data, $academicYear, $semester, &$enrolled, &$skipped) {
            foreach ($data['user_ids'] as $userId) {
                $exists = Enrollment::where('user_id', $userId)
                    ->where('module_id', $data['module_id'])
                    ->where('academic_year', $academicYear)
                    ->where('semester', $semester)
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                Enrollment::create([
                    'user_id' => $userId,
                    'module_id' => $data['module_id'],
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                ]);

                $user = User::find($userId);
                if ($user) {
                    $user->modules()->syncWithoutDetaching([$data['module_id']]);
                }

                $enrolled++;
            }
        });

        $message = "Bulk enrollment complete. {$enrolled} students enrolled.";
        if ($skipped > 0) {
            $message .= " {$skipped} already enrolled (skipped).";
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk remove students from a module (admin only).
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorize('create', Enrollment::class);

        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $module = Module::findOrFail($data['module_id']);

        $removed = 0;

        DB::transaction(function () use ($data, &$removed) {
            foreach ($data['user_ids'] as $userId) {
                $deleted = Enrollment::where('user_id', $userId)
                    ->where('module_id', $data['module_id'])
                    ->delete();

                if ($deleted) {
                    $user = User::find($userId);
                    if ($user) {
                        $user->modules()->detach($data['module_id']);
                    }
                    $removed++;
                }
            }
        });

        return back()->with('success', "{$removed} students removed from module.");
    }

    /**
     * Show bulk enrollment form.
     */
    public function bulkEnrollForm(Request $request): Response
    {
        $this->authorize('create', Enrollment::class);

        $currentYear = AcademicYear::current()->first();
        $semester = now()->month <= 6 ? '1' : '2';

        $students = User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })->whereHas('profile', function ($q) {
            $q->where('status', 'active');
        })->orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Enrollment/BulkEnroll', [
            'modules' => Module::orderBy('name')->get(['id', 'name', 'code']),
            'students' => $students,
            'academicYear' => $currentYear,
            'semester' => $semester,
        ]);
    }

    /**
     * List pending student enrollment/deregistration requests for review.
     */
    public function requests(Request $request): Response
    {
        $this->authorize('viewAny', EnrollmentRequest::class);

        $query = EnrollmentRequest::with(['user', 'module', 'reviewer'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        } else {
            $query->where('status', EnrollmentRequest::STATUS_PENDING);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        $requests = $query->paginate(25)->withQueryString();

        return Inertia::render('Enrollment/Requests', [
            'requests' => $requests,
            'filters' => $request->only('status', 'type'),
            'pendingCount' => EnrollmentRequest::pending()->count(),
        ]);
    }

    /**
     * Approve or reject a pending request. Approving an enrollment request
     * creates the real Enrollment; approving a deregistration request removes it.
     */
    public function decideRequest(Request $request, EnrollmentRequest $enrollmentRequest): RedirectResponse
    {
        $this->authorize('update', $enrollmentRequest);

        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_note' => 'nullable|string|max:500',
        ]);

        if (! $enrollmentRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $oldValues = $enrollmentRequest->getOriginal();
        $student = $enrollmentRequest->user;
        $moduleId = $enrollmentRequest->module_id;

        DB::transaction(function () use ($enrollmentRequest, $data, $request, $student, $moduleId) {
            if ($data['status'] === EnrollmentRequest::STATUS_APPROVED) {
                if ($enrollmentRequest->isEnrollment()) {
                    $alreadyEnrolled = Enrollment::where('user_id', $enrollmentRequest->user_id)
                        ->where('module_id', $moduleId)
                        ->where('academic_year', $enrollmentRequest->academic_year)
                        ->where('semester', $enrollmentRequest->semester)
                        ->exists();

                    if (! $alreadyEnrolled) {
                        Enrollment::create([
                            'user_id' => $enrollmentRequest->user_id,
                            'module_id' => $moduleId,
                            'academic_year' => $enrollmentRequest->academic_year,
                            'semester' => $enrollmentRequest->semester,
                        ]);

                        $student?->modules()->syncWithoutDetaching([$moduleId]);
                    }
                } else {
                    $enrollment = Enrollment::where('user_id', $enrollmentRequest->user_id)
                        ->where('module_id', $moduleId)
                        ->first();

                    if ($enrollment) {
                        $this->audit->logDeleted($enrollment);
                        $enrollment->delete();
                        $student?->modules()->detach($moduleId);
                    }
                }
            }

            $enrollmentRequest->update([
                'status' => $data['status'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? null,
            ]);
        });

        $this->audit->logUpdated($enrollmentRequest, $oldValues);

        if ($student) {
            $student->notify(new EnrollmentRequestDecided($enrollmentRequest->fresh()));
        }

        return back()->with('success', "Request {$data['status']}.");
    }

    /**
     * Notify staff who can review enrollment requests.
     */
    private function notifyReviewers(EnrollmentRequest $enrollmentRequest): void
    {
        $reviewers = User::role(['super-admin', 'it-support', 'registrar', 'program-coordinator', 'academic-director', 'admissions-officer'])
            ->whereDoesntHave('enrollmentRequests', fn ($q) => $q->whereKey($enrollmentRequest->user_id))
            ->get();

        if ($reviewers->isNotEmpty()) {
            Notification::send($reviewers, new EnrollmentRequestSubmitted($enrollmentRequest));
        }
    }
}
