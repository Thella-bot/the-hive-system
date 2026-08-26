<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user->hasRole('student'), 403);

        $context = $user->getCurrentSemesterContext();
        $yearLevel = $context['year_level'];
        $semester = $context['semester'];
        $currentAcademicYear = now()->format('Y');

        $enrolledModuleIds = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('academic_year', $currentAcademicYear)
            ->where('semester', $semester)
            ->pluck('module_id');

        $isAdmin = $user->hasAnyRole(['super-admin', 'it-support', 'registrar', 'program-coordinator', 'academic-director']);

        if ($isAdmin) {
            $modules = Module::with('department')->orderBy('name')->paginate(50);
        } else {
            $yearLevels = [$yearLevel];
            if ($yearLevel > 1) {
                $yearLevels[] = $yearLevel - 1;
            }

            $modules = Module::with('department')
                ->whereHas('programmes', function ($q) use ($user, $yearLevels, $semester) {
                    if ($user->programme_id) {
                        $q->where('programme_module.programme_id', $user->programme_id)
                          ->whereIn('programme_module.year_level', $yearLevels)
                          ->where('programme_module.semester', $semester);
                    }
                })
                ->orderBy('name')
                ->paginate(50);
        }

        return Inertia::render('Enrollment/Index', [
            'modules' => $modules,
            'enrolledModuleIds' => $enrolledModuleIds,
            'semesterContext' => $context,
            'isRepeatingYear' => !$isAdmin && $yearLevel > 1 && $modules->isNotEmpty(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasRole('student'), 403);

        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
        ]);

        $context = $user->getCurrentSemesterContext();
        $semester = $context['semester'] ?? (now()->month <= 6 ? '1' : '2');
        $isAdmin = $user->hasAnyRole(['super-admin', 'it-support', 'registrar', 'program-coordinator', 'academic-director']);

        if (!$isAdmin) {
            $module = Module::findOrFail($data['module_id']);

            if ($user->programme_id && $context['year_level']) {
                $yearLevels = [$context['year_level']];
                if ($context['year_level'] > 1) {
                    $yearLevels[] = $context['year_level'] - 1;
                }

                $isValidModule = $module->programmes()
                    ->wherePivot('programme_id', $user->programme_id)
                    ->whereIn('programme_module.year_level', $yearLevels)
                    ->wherePivot('semester', $context['semester'])
                    ->exists();

                abort_unless($isValidModule, 403, 'This module is not available for your current semester.');
            }

            $profile = $user->profile;
            if ($profile && $profile->cohort_id) {
                $cohort = $profile->cohort;
                if ($cohort && $cohort->max_students > 0) {
                    $cohortStudentCount = \App\Models\Profile::where('cohort_id', $cohort->id)->count();
                    if ($cohortStudentCount >= $cohort->max_students) {
                        abort(403, 'This cohort has reached its maximum enrollment capacity.');
                    }
                }
            }
        }

        Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'module_id' => $data['module_id'],
            'academic_year' => now()->format('Y'),
            'semester' => $semester,
        ]);

        $user->modules()->syncWithoutDetaching([$data['module_id']]);

        return back()->with('success', 'Module enrollment updated.');
    }

    public function destroy(Request $request, Module $module): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasRole('student'), 403);

        Enrollment::query()
            ->where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->delete();

        $user->modules()->detach($module->id);

        return back()->with('success', 'You have left the module.');
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
                // Check if already enrolled
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

                // Also sync to module_user pivot
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
}
