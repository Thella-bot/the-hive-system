<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class TranscriptController extends Controller
{
    public function show(User $student)
    {
        // Only the student themselves, admin, or instructor of their modules
        $user = auth()->user();
        if ($user->can('view-student-grades') && $user->id !== $student->id) abort(403);
        if ($user->can('manage-student-grades')) {
            $studentModuleIds = $student->modules()->pluck('id');
            $instructorModuleIds = $user->instructedModules()->pluck('id');
            if ($studentModuleIds->intersect($instructorModuleIds)->isEmpty()) abort(403);
        }

        $modules = $student->modules()->with(['gradeItems.studentGrades' => function($q) use ($student) {
            $q->where('student_id', $student->id);
        }])->get();

    // Compute weighted GPA based on module credits
    $totalGradeCreditPoints = 0;
    $totalCredits = 0;

    foreach ($modules as $module) {
        $moduleWeightedMarks = 0;
        $moduleTotalWeight = 0;

        foreach ($module->gradeItems as $item) {
            $studentGrade = $item->studentGrades->first();
            if ($studentGrade && $item->max_marks > 0) {
                $percentage = ($studentGrade->marks / $item->max_marks) * 100;
                $moduleWeightedMarks += $percentage * $item->weight;
                $moduleTotalWeight += $item->weight;
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