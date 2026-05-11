<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function store(Request $request, Assignment $assignment) {
        $this->authorize('create', Submission::class);
        $student = $request->user();

        // Ensure student is enrolled in assignment's module (avoid loading all modules)
        $isEnrolled = $student->modules()->where('modules.id', $assignment->module_id)->exists();
        if (! $isEnrolled) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|max:'.$assignment->max_file_size,
        ]);

        // Prevent duplicate submissions for the same student + assignment
        // (Business rule: one submission per assignment; uploads should create a new version instead.)
        if (Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->exists()) {
            return back()->withErrors(['file' => 'You have already submitted for this assignment.']);
        }

        $file = $request->file('file');
        $path = $file->store('private/submissions/' . $assignment->id);

        Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'file_path' => $path,
            'submitted_at' => now(),
            'is_late' => now()->gt($assignment->due_date),
        ]);
        return back()->with('success', 'Submission uploaded.');
    }

    // Instructors grade a submission
    public function update(Request $request, Submission $submission) {
        $this->authorize('update', $submission);
        $data = $request->validate([
            'grade' => 'nullable|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);
        $submission->update(array_merge($data, [
            'graded_at' => now(),
            'graded_by' => auth()->id(),
        ]));
        return back();
    }

    // Serve file securely
    public function download(Submission $submission) {
        $this->authorize('view', $submission);
        return Storage::download($submission->file_path);
    }
}