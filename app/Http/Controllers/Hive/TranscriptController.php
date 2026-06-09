<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class TranscriptController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        $modules = $student->modules()
            ->with(['gradables.submissions' => fn($q) => $q->where('student_id', $student->id)])
            ->get()
            ->map(fn($module) => $this->enrichModule($module, $student));

        return Inertia::render('Hive/Transcript/Index', [
            'student' => $student,
            'modules' => $modules,
        ]);
    }

    private function enrichModule($module, User $student)
    {
        $totalWeight = 0;
        $totalMarks = 0;
        foreach ($module->gradables as $gradable) {
            $submission = $gradable->submissions->first();
            if ($submission && $submission->grade !== null && $gradable->max_marks > 0) {
                $totalMarks += ($submission->grade / $gradable->max_marks) * 100 * $gradable->weight;
                $totalWeight += $gradable->weight;
            }
        }
        $module->totalGradables = $module->gradables->count();
        $module->gradedCount = $module->gradables->filter(fn($g) => $g->submissions->first()?->grade !== null)->count();
        $module->averageGrade = $totalWeight > 0 ? round($totalMarks / $totalWeight, 1) : null;
        return $module;
    }

    public function show(User $student)
    {
        $user = auth()->user();

        if (!$user->can('view-student-grades') && $user->id !== $student->id) abort(403);
        if ($user->can('manage-student-grades')) {
            $studentModuleIds = $student->modules()->pluck('id');
            $instructorModuleIds = $user->instructedModules()->pluck('id');
            if ($studentModuleIds->intersect($instructorModuleIds)->isEmpty()) abort(403);
        }

        $modules = $student->modules()->with(['gradables.submissions' => function($q) use ($student) {
            $q->where('student_id', $student->id);
        }])->get();

        $totalGradeCreditPoints = 0;
        $totalCredits = 0;

        foreach ($modules as $module) {
            $moduleWeightedMarks = 0;
            $moduleTotalWeight = 0;

            foreach ($module->gradables as $gradable) {
                $submission = $gradable->submissions->first();
                if ($submission && $submission->grade !== null && $gradable->max_marks > 0) {
                    $percentage = ($submission->grade / $gradable->max_marks) * 100;
                    $moduleWeightedMarks += $percentage * $gradable->weight;
                    $moduleTotalWeight += $gradable->weight;
                }
            }

            if ($moduleTotalWeight > 0) {
                $moduleFinalGrade = $moduleWeightedMarks / $moduleTotalWeight;
                $totalGradeCreditPoints += $moduleFinalGrade * $module->credits;
                $totalCredits += $module->credits;
            }
        }

        $weightedGpa = $totalCredits > 0 ? round($totalGradeCreditPoints / $totalCredits, 1) : 'N/A';

        $pdf = Pdf::loadView('pdf.transcript', [
            'student' => $student,
            'modules' => $modules,
            'gpa' => $weightedGpa,
        ]);

        return $pdf->download('Transcript_'.$student->id.'.pdf');
    }
}