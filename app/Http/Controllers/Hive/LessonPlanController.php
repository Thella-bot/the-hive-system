<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan;
use App\Models\Module;
use App\Models\TimetableSlot;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonPlanController extends Controller
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    public function index(Request $request, Module $module)
    {
        $user = $request->user();
        $query = LessonPlan::with(['creator', 'timetableSlot.room'])->forModule($module->id);

        if ($user->isStudent()) {
            $query->published();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $lessonPlans = $query->orderBy('lesson_date', 'desc')->paginate(15);

        return Inertia::render('Hive/LessonPlans/Index', [
            'module' => $module,
            'lessonPlans' => $lessonPlans,
            'filters' => $request->only('status'),
        ]);
    }

    public function create(Module $module)
    {
        $this->authorize('create', [LessonPlan::class, $module]);

        $timetableSlots = TimetableSlot::forModule($module->id)->active()->get();

        return Inertia::render('Hive/LessonPlans/Create', [
            'module' => $module,
            'timetableSlots' => $timetableSlots,
        ]);
    }

    public function store(Request $request, Module $module)
    {
        $this->authorize('create', [LessonPlan::class, $module]);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'objectives' => 'nullable|string',
            'content' => 'nullable|string',
            'resources' => 'nullable|string',
            'assessment' => 'nullable|string',
            'lesson_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'timetable_slot_id' => 'nullable|exists:timetable_slots,id',
            'status' => 'required|in:draft,published,completed,cancelled',
        ]);

        $lessonPlan = LessonPlan::create([
            'module_id' => $module->id,
            'created_by' => $request->user()->id,
            'title' => strip_tags($data['title']),
            'description' => isset($data['description']) ? strip_tags($data['description']) : null,
            'objectives' => isset($data['objectives']) ? strip_tags($data['objectives']) : null,
            'content' => isset($data['content']) ? strip_tags($data['content']) : null,
            'resources' => isset($data['resources']) ? strip_tags($data['resources']) : null,
            'assessment' => isset($data['assessment']) ? strip_tags($data['assessment']) : null,
            'lesson_date' => $data['lesson_date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'timetable_slot_id' => $data['timetable_slot_id'] ?? null,
            'status' => $data['status'],
        ]);

        $this->audit->logCreated($lessonPlan);

        return redirect()->route('hive.modules.lesson-plans.index', $module)
            ->with('success', 'Lesson plan created.');
    }

    public function show(Module $module, LessonPlan $lessonPlan)
    {
        $lessonPlan->load(['creator', 'timetableSlot.room']);

        return Inertia::render('Hive/LessonPlans/Show', [
            'module' => $module,
            'lessonPlan' => $lessonPlan,
        ]);
    }

    public function edit(Module $module, LessonPlan $lessonPlan)
    {
        $this->authorize('update', [$lessonPlan, $module]);

        $timetableSlots = TimetableSlot::forModule($module->id)->active()->get();

        return Inertia::render('Hive/LessonPlans/Edit', [
            'module' => $module,
            'lessonPlan' => $lessonPlan,
            'timetableSlots' => $timetableSlots,
        ]);
    }

    public function update(Request $request, Module $module, LessonPlan $lessonPlan)
    {
        $this->authorize('update', [$lessonPlan, $module]);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'objectives' => 'nullable|string',
            'content' => 'nullable|string',
            'resources' => 'nullable|string',
            'assessment' => 'nullable|string',
            'lesson_date' => 'sometimes|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'timetable_slot_id' => 'nullable|exists:timetable_slots,id',
            'status' => 'sometimes|in:draft,published,completed,cancelled',
        ]);

        // Sanitize text fields
        $data['title'] = isset($data['title']) ? strip_tags($data['title']) : $lessonPlan->title;
        $data['description'] = isset($data['description']) ? strip_tags($data['description']) : $lessonPlan->description;
        $data['objectives'] = isset($data['objectives']) ? strip_tags($data['objectives']) : $lessonPlan->objectives;
        $data['content'] = isset($data['content']) ? strip_tags($data['content']) : $lessonPlan->content;
        $data['resources'] = isset($data['resources']) ? strip_tags($data['resources']) : $lessonPlan->resources;
        $data['assessment'] = isset($data['assessment']) ? strip_tags($data['assessment']) : $lessonPlan->assessment;

        $oldValues = $lessonPlan->getOriginal();
        $lessonPlan->update($data);

        $this->audit->logUpdated($lessonPlan, $oldValues);

        return redirect()->route('hive.modules.lesson-plans.index', $module)
            ->with('success', 'Lesson plan updated.');
    }

    public function destroy(Module $module, LessonPlan $lessonPlan)
    {
        $this->authorize('delete', [$lessonPlan, $module]);

        $this->audit->logDeleted($lessonPlan);

        $lessonPlan->delete();

        return redirect()->route('hive.modules.lesson-plans.index', $module)
            ->with('success', 'Lesson plan deleted.');
    }
}
