<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\Gradable;
use App\Models\Module;
use App\Models\StudentProgress;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentProgressController extends Controller
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    public function index(Request $request, Module $module)
    {
        $user = $request->user();

        if ($user->isStudent()) {
            return $this->studentProgress($request, $module);
        }

        return $this->instructorProgress($request, $module);
    }

    private function studentProgress(Request $request, Module $module)
    {
        $user = $request->user();
        $progress = StudentProgress::forUser($user->id)
            ->forModule($module->id)
            ->get();

        $gradables = Gradable::forModule($module->id)->get();
        $completedCount = $progress->where('status', 'completed')->count();
        $totalItems = $gradables->count() + CourseMaterial::forModule($module->id)->published()->count();
        $progressPercent = $totalItems > 0 ? round(($completedCount / $totalItems) * 100) : 0;

        return Inertia::render('Hive/Progress/Student', [
            'module' => $module,
            'progress' => $progress,
            'gradables' => $gradables,
            'completedCount' => $completedCount,
            'totalItems' => $totalItems,
            'progressPercent' => $progressPercent,
        ]);
    }

    private function instructorProgress(Request $request, Module $module)
    {
        $students = $module->students;
        $gradables = Gradable::forModule($module->id)->get();

        $studentProgress = [];
        foreach ($students as $student) {
            $completed = StudentProgress::forUser($student->id)
                ->forModule($module->id)
                ->completed()
                ->count();
            $totalItems = $gradables->count();
            $percent = $totalItems > 0 ? round(($completed / $totalItems) * 100) : 0;

            $studentProgress[] = [
                'student' => $student,
                'completed' => $completed,
                'total' => $totalItems,
                'percent' => $percent,
            ];
        }

        return Inertia::render('Hive/Progress/Instructor', [
            'module' => $module,
            'students' => $studentProgress,
            'gradables' => $gradables,
        ]);
    }

    public function update(Request $request, Module $module)
    {
        $user = $request->user();
        $data = $request->validate([
            'item_type' => 'required|in:gradable,material,lesson_plan',
            'item_id' => 'required|integer',
            'status' => 'required|in:not_started,in_progress,completed',
            'score' => 'nullable|numeric|min:0|max:100',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        $progress = StudentProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'item_type' => $data['item_type'],
                'item_id' => $data['item_id'],
            ],
            [
                'status' => $data['status'],
                'score' => $data['score'] ?? null,
                'time_spent' => $data['time_spent'] ?? 0,
                'completed_at' => $data['status'] === 'completed' ? now() : null,
            ]
        );

        $this->audit->logUpdated($progress, $progress->getOriginal());

        return back()->with('success', 'Progress updated.');
    }
}
