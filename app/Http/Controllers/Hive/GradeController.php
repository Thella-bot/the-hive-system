<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Gradable;
use App\Models\Module;
use App\Models\Submission;
use App\Services\AuditService;
use App\Services\CsvExporter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly CsvExporter $csv,
    ) {}
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Gradable::class);
        $user = $request->user();

        if ($user->hasRole('student')) {
            return Inertia::render('Hive/Grades/StudentIndex', [
                'modules' => $this->studentModules($user),
            ]);
        }

        $modules = $user->isAdmin()
            ? Module::with(['programme', 'gradables.submissions'])->get()
            : $user->instructedModules()->with(['programme', 'gradables.submissions'])->get();

        return Inertia::render('Hive/Grades/InstructorIndex', ['modules' => $modules]);
    }

    /**
     * Export grades to CSV.
     *
     * A student can only ever export their own grades; staff export the modules
     * they are permitted to see, which is the same set the gradebook renders.
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Gradable::class);
        $user = $request->user();

        $rows = $user->hasRole('student')
            ? $this->studentGradeRows($user)
            : $this->instructorGradeRows($user, $request);

        $this->audit->log('exported', $user, [
            'resource' => 'grades',
            'row_count' => count($rows),
        ]);

        return $this->csv->download(
            $rows,
            ['Student', 'Student Number', 'Module', 'Module Code', 'Assessment', 'Type', 'Score', 'Max', 'Percentage', 'Weight', 'Status', 'Submitted', 'Graded'],
            'grades'
        );
    }

    // Show grade management for a module
    public function manage(Module $module)
    {
        $this->authorize('manage', $module);
        $user = auth()->user();

        $module->load(['gradables.submissions.student']);

        return Inertia::render('Hive/Grades/ModuleGrades', ['module' => $module]);
    }

    /**
     * The student's modules with their gradables and this student's submission.
     */
    private function studentModules($user): array
    {
        $enrollmentIds = $user->enrollments()
            ->withTrashed()
            ->pluck('module_id')
            ->toArray();

        $modules = Module::with(['gradables' => function ($query) {
            $query->orderBy('due_date', 'desc');
        }])->whereIn('id', $enrollmentIds)->get();

        $gradableIds = $modules->flatMap(fn ($module) => $module->gradables->pluck('id'));
        $submissions = Submission::whereIn('gradable_id', $gradableIds)
            ->where('student_id', $user->id)
            ->get()
            ->keyBy('gradable_id');

        return $modules->map(function ($module) use ($submissions) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'gradables' => $module->gradables->map(function ($gradable) use ($submissions) {
                    return array_merge($gradable->toArray(), [
                        'submission' => $submissions->get($gradable->id)?->toArray(),
                    ]);
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function studentGradeRows($user): array
    {
        $rows = [];
        $enrollmentIds = $user->enrollments()->withTrashed()->pluck('module_id')->toArray();

        if ($enrollmentIds === []) {
            return $rows;
        }

        $gradableIds = Gradable::whereIn('module_id', $enrollmentIds)->pluck('id');

        $submissions = Submission::whereIn('gradable_id', $gradableIds)
            ->where('student_id', $user->id)
            ->with('gradable.module')
            ->get();

        foreach ($submissions as $submission) {
            $rows[] = $this->gradeRow(
                $user->name,
                $user->student_number,
                $submission->gradable?->module?->name,
                $submission->gradable?->module?->code,
                $submission,
            );
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function instructorGradeRows($user, Request $request): array
    {
        $query = $user->isAdmin()
            ? Module::with(['gradables.submissions.student'])
            : $user->instructedModules()->with(['gradables.submissions.student']);

        if ($request->filled('module_id')) {
            $query->whereKey($request->integer('module_id'));
        }

        $rows = [];

        foreach ($query->get() as $module) {
            foreach ($module->gradables as $gradable) {
                foreach ($gradable->submissions as $submission) {
                    $rows[] = $this->gradeRow(
                        $submission->student?->name,
                        $submission->student?->student_number,
                        $module->name,
                        $module->code,
                        $submission,
                    );
                }
            }
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function gradeRow(?string $studentName, ?string $studentNumber, ?string $moduleName, ?string $moduleCode, Submission $submission): array
    {
        $gradable = $submission->gradable;
        $type = $gradable?->type instanceof \BackedEnum ? $gradable->type->value : (string) $gradable?->type;
        $max = $gradable?->max_marks;

        // A percentage is only meaningful when the assessment declares its
        // maximum mark, so it stays blank rather than showing a misleading 0.
        $percentage = '';
        if ($submission->grade !== null && $max !== null && (float) $max > 0) {
            $percentage = number_format(((float) $submission->grade / (float) $max) * 100, 2, '.', '');
        }

        return [
            'student' => $studentName,
            'student_number' => $studentNumber,
            'module' => $moduleName,
            'module_code' => $moduleCode,
            'assessment' => $gradable?->title,
            'type' => $type,
            'score' => $submission->grade === null ? '' : (string) $submission->grade,
            'max' => $max === null ? '' : (string) $max,
            'percentage' => $percentage,
            'weight' => $gradable?->weight === null ? '' : (string) $gradable->weight,
            'status' => $this->submissionStatus($submission),
            'submitted' => $this->csv->date($submission->submitted_at, 'Y-m-d H:i'),
            'graded' => $this->csv->date($submission->graded_at, 'Y-m-d H:i'),
        ];
    }

    private function submissionStatus(Submission $submission): string
    {
        if ($submission->grade !== null) {
            return 'graded';
        }

        return $submission->submitted_at ? 'awaiting grading' : 'not submitted';
    }
}