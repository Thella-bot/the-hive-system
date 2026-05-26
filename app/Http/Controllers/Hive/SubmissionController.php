<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Notifications\SubmissionGraded;
use App\Notifications\SubmissionReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Submission::class, 'submission');
    }

    public function store(Request $request, Assignment $assignment)
    {
        $this->authorize('create', [Submission::class, $assignment]);
        $student = $request->user();

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . $assignment->max_file_size,
                'mimes:' . $assignment->allowed_types,
            ],
        ]);

        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'file_path' => $request->file('file')->store('private/submissions/' . $assignment->id),
                'submitted_at' => now(),
                'is_late' => now()->gt($assignment->due_date),
            ]
        );

        $assignment->instructor->notify(new SubmissionReceived($submission));

        return back()->with('success', 'Submission uploaded successfully.');
    }

    // Instructors grade a submission
    public function update(Request $request, Submission $submission)
    {
        $this->authorize('update', $submission);
        $data = $request->validate([
            'grade' => 'nullable|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);
        $submission->update(array_merge($data, [
            'graded_at' => now(),
            'graded_by' => auth()->id(),
        ]));

        $submission->student->notify(new SubmissionGraded($submission));

        return back();
    }

    // Serve file securely
    public function download(Submission $submission)
    {
        $this->authorize('view', $submission);
        return Storage::download($submission->file_path);
    }
}
