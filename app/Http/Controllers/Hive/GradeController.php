<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Gradable;
use App\Models\Module;
use App\Models\Submission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('student')) {
            // Student: Show enrolled modules with gradables and their grades
            $moduleIds = $user->modules()->pluck('module_id')->toArray();

            $modules = Module::with(['gradables' => function ($query) {
                $query->orderBy('due_date', 'desc');
            }])->whereIn('id', $moduleIds)->get();

            // Attach submissions for each gradable
            foreach ($modules as $module) {
                foreach ($module->gradables as $gradable) {
                    $submission = Submission::where('gradable_id', $gradable->id)
                        ->where('student_id', $user->id)
                        ->first();
                    $gradable->submission = $submission;
                }
            }

            return Inertia::render('Hive/Grades/StudentIndex', ['modules' => $modules]);
        } else {
            // Instructor/Admin: select module to manage grades
            $isAdmin = $user->isAdmin();

            if ($isAdmin) {
                $modules = Module::with(['programme', 'gradables.submissions'])->get();
            } else {
                $modules = $user->instructedModules()->with(['programme', 'gradables.submissions'])->get();
            }

            return Inertia::render('Hive/Grades/InstructorIndex', ['modules' => $modules]);
        }
    }

    // Show grade management for a module
    public function manage(Module $module)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        abort_unless(
            $isAdmin || $module->instructors()->where('user_id', $user->id)->exists(),
            403
        );

        $module->load(['gradables.submissions.student']);

        return Inertia::render('Hive/Grades/ModuleGrades', ['module' => $module]);
    }
}