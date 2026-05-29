<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Gradable;
use App\Models\GradableAttachment;
use App\Models\GradableQuestion;
use App\Models\GradableQuestionOption;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class GradableController extends Controller
{
    /**
     * Display module selection page for assessments.
     */
    public function moduleSelect(Request $request, string $type): Response
    {
        $user = auth()->user();

        // Get modules based on user role
        if ($user->hasRole('student')) {
            $modules = $user->modules()->with('programme')->get();
        } elseif ($user->hasRole('academic_staff')) {
            $modules = $user->instructedModules()->with('programme')->get();
        } else {
            $modules = Module::with('programme')->get();
        }

        $typeLabels = [
            'quiz' => 'Quizzes',
            'test' => 'Tests',
            'assignment' => 'Assignments',
            'mid_term_exam' => 'Mid-Term Exams',
            'final_exam' => 'Final Exams',
        ];

        return Inertia::render('Hive/Gradables/ModuleSelect', [
            'modules' => $modules,
            'type' => $type,
            'typeLabel' => $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $query = Gradable::with(['module', 'instructor']);

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by module if provided
        if ($request->has('module_id')) {
            $query->where('module_id', $request->module_id);
        }

        // Students can only see gradables from their enrolled modules
        if ($user->hasRole('student')) {
            $enrolledModuleIds = $user->modules()->pluck('module_id');
            $query->whereIn('module_id', $enrolledModuleIds);
        }

        $gradables = $query->orderBy('due_date', 'desc')->get();

        // Determine user abilities for UI
        $canCreate = Gate::allows('create', Gradable::class);

        return Inertia::render('Hive/Gradables/Index', [
            'gradables' => $gradables,
            'canCreate' => $canCreate,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Gradable::class);

        $user = auth()->user();

        // Instructors see only their assigned modules
        if ($user->hasRole('academic_staff')) {
            $modules = $user->instructedModules()->get();
        } else {
            // Admins see all modules
            $modules = Module::with('programme')->get();
        }

        $gradableTypes = \App\Enums\GradableType::cases();
        $submissionTypes = [
            ['value' => Gradable::SUBMISSION_TYPE_FILE_UPLOAD, 'label' => 'File Upload'],
            ['value' => Gradable::SUBMISSION_TYPE_ONLINE_FILLABLE, 'label' => 'Online (Fill in the Blank / Short Answer)'],
            ['value' => Gradable::SUBMISSION_TYPE_ONLINE_MCQ, 'label' => 'Online (Multiple Choice)'],
        ];

        return Inertia::render('Hive/Gradables/Create', [
            'modules' => $modules,
            'gradableTypes' => array_map(fn($type) => $type->value, $gradableTypes),
            'submissionTypes' => $submissionTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Gradable::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'submission_type' => 'required|string|in:file_upload,online_fillable,online_multiple_choice',
            'module_id' => 'required|exists:modules,id',
            'due_date' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:0',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'show_correct_answers' => 'nullable|boolean',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'description' => 'nullable|string',
            'max_marks' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:100',
            'max_file_size' => 'nullable|integer|min:1|max:102400', // KB, max 100MB
            'allowed_types' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*.title' => 'required|string|max:255',
            'attachments.*.file' => 'required|file|max:51200', // 50MB max per file
        ]);

        $validated['instructor_id'] = auth()->id();

        // Convert boolean strings
        $validated['show_correct_answers'] = $request->boolean('show_correct_answers');
        $validated['shuffle_questions'] = $request->boolean('shuffle_questions');
        $validated['shuffle_options'] = $request->boolean('shuffle_options');

        $gradable = Gradable::create($validated);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $idx => $file) {
                $title = $request->input("attachments.{$idx}.title") ?? $file->getClientOriginalName();
                $path = $file->store('private/gradables/' . $gradable->id);

                $gradable->attachments()->create([
                    'title' => $title,
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('hive.gradables.edit', $gradable->id)->with('success', 'Assessment created. Now add your questions.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gradable $gradable): Response
    {
        $this->authorize('view', $gradable);

        $user = auth()->user();
        $isInstructor = $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff']);
        $isStudent = $user->hasRole('student');

        // Load appropriate relations
        $relations = ['module', 'instructor', 'attachments.uploader'];
        if ($isInstructor) {
            $relations[] = 'questions.options';
            $relations[] = 'submissions.student';
        }

        $gradable->load($relations);

        // Get student's submission if exists (for students)
        $studentSubmission = null;
        $studentAnswers = [];
        if ($isStudent) {
            $studentSubmission = $gradable->submissions()
                ->where('student_id', $user->id)
                ->first();

            // Get student's answers for online assessments
            if ($gradable->isOnlineAssessment()) {
                $studentAnswers = $gradable->answers()
                    ->where('student_id', $user->id)
                    ->get()
                    ->keyBy('gradable_question_id');
            }
        }

        return Inertia::render('Hive/Gradables/Show', [
            'gradable' => $gradable,
            'isInstructor' => $isInstructor,
            'isStudent' => $isStudent,
            'studentSubmission' => $studentSubmission,
            'studentAnswers' => $studentAnswers,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gradable $gradable): Response
    {
        $this->authorize('update', $gradable);

        $user = auth()->user();

        if ($user->hasRole('academic_staff')) {
            $modules = $user->instructedModules()->get();
        } else {
            $modules = Module::with('programme')->get();
        }

        $gradableTypes = \App\Enums\GradableType::cases();
        $submissionTypes = [
            ['value' => Gradable::SUBMISSION_TYPE_FILE_UPLOAD, 'label' => 'File Upload'],
            ['value' => Gradable::SUBMISSION_TYPE_ONLINE_FILLABLE, 'label' => 'Online (Fill in the Blank / Short Answer)'],
            ['value' => Gradable::SUBMISSION_TYPE_ONLINE_MCQ, 'label' => 'Online (Multiple Choice)'],
        ];

        $gradable->load('attachments', 'questions.options');

        return Inertia::render('Hive/Gradables/Edit', [
            'gradable' => $gradable,
            'modules' => $modules,
            'gradableTypes' => array_map(fn($type) => $type->value, $gradableTypes),
            'submissionTypes' => $submissionTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gradable $gradable): RedirectResponse
    {
        $this->authorize('update', $gradable);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'submission_type' => 'required|string|in:file_upload,online_fillable,online_multiple_choice',
            'module_id' => 'required|exists:modules,id',
            'due_date' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:0',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'show_correct_answers' => 'nullable|boolean',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'description' => 'nullable|string',
            'max_marks' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:100',
            'max_file_size' => 'nullable|integer|min:1|max:102400',
            'allowed_types' => 'nullable|string',
        ]);

        $validated['show_correct_answers'] = $request->boolean('show_correct_answers');
        $validated['shuffle_questions'] = $request->boolean('shuffle_questions');
        $validated['shuffle_options'] = $request->boolean('shuffle_options');

        $gradable->update($validated);

        return back()->with('success', 'Assessment updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gradable $gradable): RedirectResponse
    {
        $this->authorize('delete', $gradable);

        // Delete attachment files
        foreach ($gradable->attachments as $attachment) {
            Storage::delete($attachment->file_path);
        }

        $gradable->delete();

        return redirect()->route('hive.gradables.index')->with('success', 'Assessment deleted.');
    }

    /**
     * Store a question for an online assessment.
     */
    public function storeQuestion(Request $request, Gradable $gradable): RedirectResponse
    {
        $this->authorize('update', $gradable);

        if (!$gradable->isOnlineAssessment()) {
            return back()->with('error', 'Questions can only be added to online assessments.');
        }

        $validated = $request->validate([
            'type' => 'required|string|in:multiple_choice,fill_in_blank,short_answer,essay',
            'question_text' => 'required|string',
            'points' => 'nullable|integer|min:1',
            'is_required' => 'nullable|boolean',
            'explanation' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
        ]);

        $question = $gradable->questions()->create([
            'type' => $validated['type'],
            'question_text' => $validated['question_text'],
            'points' => $validated['points'] ?? 1,
            'is_required' => $request->boolean('is_required', true),
            'explanation' => $validated['explanation'] ?? null,
            'sort_order' => $gradable->questions()->max('sort_order') + 1,
        ]);

        // Handle options for MCQ
        if ($validated['type'] === 'multiple_choice' && !empty($validated['options'])) {
            foreach ($validated['options'] as $idx => $option) {
                $question->options()->create([
                    'option_text' => $option['option_text'],
                    'is_correct' => !empty($option['is_correct']),
                    'sort_order' => $idx,
                ]);
            }
        }

        return back()->with('success', 'Question added.');
    }

    /**
     * Update a question.
     */
    public function updateQuestion(Request $request, Gradable $gradable, GradableQuestion $question): RedirectResponse
    {
        $this->authorize('update', $gradable);

        if ($question->gradable_id !== $gradable->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|string|in:multiple_choice,fill_in_blank,short_answer,essay',
            'question_text' => 'required|string',
            'points' => 'nullable|integer|min:1',
            'is_required' => 'nullable|boolean',
            'explanation' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'nullable|boolean',
        ]);

        $question->update([
            'type' => $validated['type'],
            'question_text' => $validated['question_text'],
            'points' => $validated['points'] ?? 1,
            'is_required' => $request->boolean('is_required', true),
            'explanation' => $validated['explanation'] ?? null,
        ]);

        // Update options for MCQ
        if ($validated['type'] === 'multiple_choice' && !empty($validated['options'])) {
            // Delete old options
            $question->options()->delete();

            foreach ($validated['options'] as $idx => $option) {
                $question->options()->create([
                    'option_text' => $option['option_text'],
                    'is_correct' => !empty($option['is_correct']),
                    'sort_order' => $idx,
                ]);
            }
        }

        return back()->with('success', 'Question updated.');
    }

    /**
     * Delete a question.
     */
    public function deleteQuestion(Gradable $gradable, GradableQuestion $question): RedirectResponse
    {
        $this->authorize('update', $gradable);

        if ($question->gradable_id !== $gradable->id) {
            abort(403);
        }

        $question->options()->delete();
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    /**
     * Upload an attachment to a gradable.
     */
    public function uploadAttachment(Request $request, Gradable $gradable): RedirectResponse
    {
        $this->authorize('update', $gradable);

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:51200', // 50MB
        ]);

        $file = $request->file('file');
        $path = $file->store('private/gradables/' . $gradable->id);

        $gradable->attachments()->create([
            'title' => $request->input('title'),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(Gradable $gradable, GradableAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $gradable);

        if ($attachment->gradable_id !== $gradable->id) {
            abort(403);
        }

        Storage::delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Gradable $gradable, GradableAttachment $attachment)
    {
        $this->authorize('view', $gradable);

        if ($attachment->gradable_id !== $gradable->id) {
            abort(403);
        }

        return Storage::download($attachment->file_path, $attachment->title);
    }

    /**
     * Submit answers for an online assessment.
     */
    public function submitOnline(Request $request, Gradable $gradable): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->hasRole('student')) {
            abort(403, 'Only students can submit online assessments.');
        }

        if (!$gradable->isOnlineAssessment()) {
            return back()->with('error', 'This assessment does not accept online submissions.');
        }

        if ($gradable->due_date && now()->gt($gradable->due_date)) {
            return back()->with('error', 'The deadline for this assessment has passed.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:gradable_questions,id',
            'answers.*.option_id' => 'nullable|exists:gradable_question_options,id',
            'answers.*.answer_text' => 'nullable|string',
        ]);

        foreach ($validated['answers'] as $answerData) {
            $question = GradableQuestion::find($answerData['question_id']);

            // Auto-grade MCQ
            $isCorrect = null;
            $pointsAwarded = 0;

            if ($question->type === 'multiple_choice' && !empty($answerData['option_id'])) {
                $selectedOption = GradableQuestionOption::find($answerData['option_id']);
                $isCorrect = $selectedOption && $selectedOption->is_correct;
                $pointsAwarded = $isCorrect ? $question->points : 0;
            }

            GradableAnswer::updateOrCreate(
                [
                    'gradable_id' => $gradable->id,
                    'student_id' => $user->id,
                    'gradable_question_id' => $question->id,
                ],
                [
                    'gradable_question_option_id' => $answerData['option_id'] ?? null,
                    'answer_text' => $answerData['answer_text'] ?? null,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $pointsAwarded,
                    'answered_at' => now(),
                ]
            );
        }

        // Create submission record
        Submission::updateOrCreate(
            [
                'gradable_id' => $gradable->id,
                'student_id' => $user->id,
            ],
            [
                'submitted_at' => now(),
                'is_late' => now()->gt($gradable->due_date),
            ]
        );

        return back()->with('success', 'Answers submitted successfully!');
    }
}