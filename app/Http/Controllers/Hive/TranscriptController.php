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
        if ($user->hasRole('student') && $user->id !== $student->id) abort(403);
        if ($user->hasRole('instructor')) {
            $studentModuleIds = $student->modules()->pluck('id');
            $instructorModuleIds = $user->instructedModules()->pluck('id');
            if ($studentModuleIds->intersect($instructorModuleIds)->isEmpty()) abort(403);
        }

        $modules = $student->modules()->with(['gradeItems.studentGrades' => function($q) use ($student) {
            $q->where('student_id', $student->id);
        }])->get();

        // Compute GPA as simple average of weighted marks (simplified)
        $totalWeightedMarks = 0; $totalWeight = 0;
        foreach ($modules as $module) {
            foreach ($module->gradeItems as $item) {
                $studentGrade = $item->studentGrades->first();
                if ($studentGrade && $item->max_marks > 0) {
                    $percentage = ($studentGrade->marks / $item->max_marks) * 100;
                    $totalWeightedMarks += $percentage * $item->weight;
                    $totalWeight += $item->weight;
                }
            }
        }
        $gpa = $totalWeight > 0 ? round($totalWeightedMarks / $totalWeight, 1) : 'N/A';

        $pdf = Pdf::loadView('pdf.transcript', [
            'student' => $student,
            'modules' => $modules,
            'gpa' => $gpa,
        ]);

        return $pdf->download('Transcript_'.$student->id.'.pdf');
    }
}