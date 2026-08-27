<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Gradable;
use App\Models\Submission;
use App\Notifications\SubmissionGraded;
use App\Notifications\SubmissionReceived;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function __construct(
        protected AuditService $audit,
    ) {
        $this->authorizeResource(Submission::class, 'submission');
    }

    public function store(Request $request, Gradable $gradable)
    {
        $this->authorize('create', [Submission::class, $gradable]);
        $student = $request->user();

        // Build validation rules dynamically
        $rules = ['required', 'file'];
        if ($gradable->max_file_size) {
            $rules[] = 'max:' . $gradable->max_file_size;
        }
        $allowedTypes = $gradable->allowed_types ?: 'pdf,doc,docx,jpg,jpeg,png,txt';
        $rules[] = 'mimes:' . $allowedTypes;

        $request->validate(['file' => $rules]);

        $existingSubmission = Submission::where('gradable_id', $gradable->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission && $existingSubmission->file_path) {
            Storage::delete($existingSubmission->file_path);
        }

        $submission = Submission::updateOrCreate(
            [
                'gradable_id' => $gradable->id,
                'student_id' => $student->id,
            ],
            [
                'file_path' => $request->file('file')->store('private/submissions/' . $gradable->id),
                'submitted_at' => now(),
                'is_late' => now()->gt($gradable->due_date),
            ]
        );

        $this->audit->logCreated($submission);
        $gradable->instructor->notify(new SubmissionReceived($submission));

        return back()->with('success', 'Submission uploaded successfully.');
    }

    public function grade(Submission $submission)
    {
        $this->authorize('grade', $submission);

        $submission->load('gradable.module', 'student');

        return Inertia::render('Hive/Submissions/Grade', [
            'submission' => $submission,
        ]);
    }

    public function storeGrade(Request $request, Submission $submission)
    {
        $this->authorize('grade', $submission);

        $maxMarks = $submission->gradable->max_marks ?? 100;

        $request->validate([
            'grade' => ['required', 'numeric', 'min:0', 'max:' . $maxMarks],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        $feedback = $request->feedback ? strip_tags($request->feedback) : null;

        $submission->update([
            'grade' => $request->grade,
            'feedback' => $feedback,
            'graded_at' => now(),
        ]);

        $this->audit->logUpdated($submission);
        $submission->student->notify(new SubmissionGraded($submission));

        return redirect()->route('hive.dashboard')->with('success', 'Grade submitted successfully.');
    }

    // Instructors grade a submission
    public function update(Request $request, Submission $submission)
    {
        $this->authorize('update', $submission);

        $maxMarks = $submission->gradable->max_marks ?? 100;

        $data = $request->validate([
            'grade' => 'nullable|numeric|min:0|max:' . $maxMarks,
            'feedback' => 'nullable|string|max:5000',
        ]);

        $data['feedback'] = isset($data['feedback']) ? strip_tags($data['feedback']) : null;

        $submission->update(array_merge($data, [
            'graded_at' => now(),
            'graded_by' => auth()->id(),
        ]));

        $this->audit->logUpdated($submission);
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