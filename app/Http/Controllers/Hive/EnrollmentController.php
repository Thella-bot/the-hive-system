<?php

declare(strict_types=1);

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $enrollments = $query->orderByDesc('created_at')->paginate(50);

        return Inertia::render('Enrollment/AdminIndex', [
            'enrollments' => $enrollments,
            'modules' => Module::orderBy('name')->get(['id', 'name', 'code']),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'filters' => $request->only('module_id', 'academic_year', 'semester'),
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

        return Inertia::render('Enrollment/Index', [
            'modules' => $availableModules,
            'enrolledModuleIds' => $enrolledModuleIds,
            'semesterContext' => [
                'year_level' => $yearLevel,
                'semester' => $semester,
            ],
            'isRepeatingYear' => $isRepeatingYear,
        ]);
    }

    /**
     * Enroll self in a module.
     */
    public function studentStore(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('viewStudent', Enrollment::class);

        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
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
                    return back()->with('error', 'You cannot enroll in a module outside your current semester.');
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

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'module_id' => $data['module_id'],
            'academic_year' => $currentYearName,
            'semester' => $semester,
        ]);

        $user->modules()->syncWithoutDetaching([$data['module_id']]);

        $this->audit->logCreated($enrollment);

        return back()->with('success', 'Enrolled successfully.');
    }

    /**
     * Drop a module by module_id.
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

        $this->audit->logDeleted($enrollment);
        $enrollment->delete();

        $user->modules()->detach($module->id);

        return back()->with('success', 'You have left the module.');
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
}
