<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\GradeItem;
use App\Models\StudentGrade;
use App\Models\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        if ($user->hasRole('student')) {
            // Show grades for enrolled modules
            $modules = $user->modules()->with(['gradeItems.studentGrades' => function($q) use ($user) {
                $q->where('student_id', $user->id);
            }])->get();
            return Inertia::render('Hive/Grades/StudentIndex', ['modules' => $modules]);
        } else {
            // Instructor/Admin: select module to manage
            $modules = $user->hasRole('admin') ? Module::all() : $user->instructedModules;
            return Inertia::render('Hive/Grades/InstructorIndex', ['modules' => $modules]);
        }
    }

    // Show grade item management for a module
    public function manage(Module $module) {
        // authorize that the user can manage grades for this module
        // If no grade items exist yet, use a lightweight check based on module instructors.
        $hasGradeItems = $module->gradeItems()->exists();
        if ($hasGradeItems) {
            $gradeItem = $module->gradeItems()->first();
            $this->authorize('update', $gradeItem);
        } else {
            // Only instructors of this module (or admin) can manage grading.
            abort_unless(
                auth()->user()->hasRole('admin') ||
                (auth()->user()->hasRole('instructor') && $module->instructors()->where('user_id', auth()->id())->exists()),
                403
            );
        }

        $module->load('gradeItems.studentGrades.student');
        return Inertia::render('Hive/Grades/Manage', ['module' => $module]);
    }

    // Store a grade item
    public function storeItem(Request $request, Module $module) {
        $data = $request->validate([
            'name'=>'required','type'=>'required','max_marks'=>'required|numeric','weight'=>'required|numeric'
        ]);
        $module->gradeItems()->create($data);
        return back();
    }

    // Enter/update student grade
    public function storeStudentGrade(Request $request, GradeItem $gradeItem) {
        $request->validate([
            'student_id'=>'required|exists:users,id',
            'marks'=>'required|numeric|max:'.$gradeItem->max_marks,
            'comments'=>'nullable|string'
        ]);
        StudentGrade::updateOrCreate(
            ['grade_item_id'=>$gradeItem->id,'student_id'=>$request->student_id],
            ['marks'=>$request->marks,'comments'=>$request->comments]
        );
        return back();
    }
}
