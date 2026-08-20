<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $modules = Module::with('department')->orderBy('name')->get();
        } else {
            $modules = Module::with('department')
                ->whereHas('programmes', function ($q) use ($user, $yearLevel, $semester) {
                    if ($user->programme_id && $yearLevel) {
                        $q->where('programme_module.programme_id', $user->programme_id)
                          ->where('programme_module.year_level', $yearLevel)
                          ->where('programme_module.semester', $semester);
                    }
                })
                ->orderBy('name')
                ->get();
        }

        return Inertia::render('Enrollment/Index', [
            'modules' => $modules,
            'enrolledModuleIds' => $enrolledModuleIds,
            'semesterContext' => $context,
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
                $isValidModule = $module->programmes()
                    ->wherePivot('programme_id', $user->programme_id)
                    ->wherePivot('year_level', $context['year_level'])
                    ->wherePivot('semester', $context['semester'])
                    ->exists();

                abort_unless($isValidModule, 403, 'This module is not available for your current semester.');
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
}
