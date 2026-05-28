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

        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->pluck('module_id');

        return Inertia::render('Enrollment/Index', [
            'modules' => Module::with('department')->orderBy('name')->get(),
            'enrolledModuleIds' => $enrollments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasRole('student'), 403);

        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
        ]);

        Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'module_id' => $data['module_id'],
            'academic_year' => now()->format('Y'),
            'semester' => now()->month <= 6 ? '1' : '2',
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
