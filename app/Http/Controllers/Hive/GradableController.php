<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Gradable;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

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
            'mid-term_exam' => 'Mid-Term Exams',
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

        return Inertia::render('Hive/Gradables/Create', [
            'modules' => $modules,
            'gradableTypes' => array_map(fn($type) => $type->value, $gradableTypes),
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
            'module_id' => 'required|exists:modules,id',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'max_marks' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['instructor_id'] = auth()->id();

        Gradable::create($validated);

        return redirect()->route('hive.gradables.index')->with('success', 'Assessment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gradable $gradable): Response
    {
        $this->authorize('view', $gradable);

        $gradable->load(['module', 'instructor', 'submissions']);

        $user = auth()->user();
        $isInstructor = $user->hasAnyRole(['super-admin', 'school-admin', 'academic_staff']);

        // Get student's submission if exists (for students)
        $studentSubmission = null;
        if ($user->hasRole('student')) {
            $studentSubmission = $gradable->submissions()
                ->where('student_id', $user->id)
                ->first();
        }

        return Inertia::render('Hive/Gradables/Show', [
            'gradable' => $gradable,
            'isInstructor' => $isInstructor,
            'studentSubmission' => $studentSubmission,
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

        return Inertia::render('Hive/Gradables/Edit', [
            'gradable' => $gradable,
            'modules' => $modules,
            'gradableTypes' => array_map(fn($type) => $type->value, $gradableTypes),
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
            'module_id' => 'required|exists:modules,id',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'max_marks' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0|max:100',
        ]);

        $gradable->update($validated);

        return redirect()->route('hive.gradables.index')->with('success', 'Assessment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gradable $gradable): RedirectResponse
    {
        $this->authorize('delete', $gradable);

        $gradable->delete();

        return redirect()->route('hive.gradables.index')->with('success', 'Assessment deleted.');
    }
}