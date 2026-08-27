<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Room;
use App\Models\TimetableSlot;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimetableController extends Controller
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = TimetableSlot::with(['module', 'instructor', 'room']);

        if ($request->filled('module_id')) {
            $query->forModule($request->input('module_id'));
        }

        if ($request->filled('instructor_id')) {
            $query->forInstructor($request->input('instructor_id'));
        }

        if ($request->filled('day')) {
            $query->forDay($request->input('day'));
        }

        if ($request->filled('academic_year')) {
            $query->forAcademicYear($request->input('academic_year'));
        } else {
            $query->forAcademicYear(date('Y') . '/' . (date('Y') + 1));
        }

        if ($request->filled('semester')) {
            $query->forSemester($request->input('semester'));
        }

        if ($user->isStudent()) {
            $enrolledModuleIds = $user->modules()->pluck('id');
            $query->whereIn('module_id', $enrolledModuleIds);
        } elseif ($user->isFaculty() && !$user->isAdmin()) {
            $query->forInstructor($user->id);
        }

        $slots = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        return Inertia::render('Hive/Timetable/Index', [
            'slots' => $slots,
            'filters' => $request->only('module_id', 'instructor_id', 'day', 'academic_year', 'semester'),
            'modules' => Module::orderBy('name')->get(['id', 'name', 'code']),
            'rooms' => Room::active()->orderBy('name')->get(),
            'days' => TimetableSlot::DAYS,
        ]);
    }

    public function create()
    {
        $this->authorize('create', TimetableSlot::class);

        return Inertia::render('Hive/Timetable/Create', [
            'modules' => Module::orderBy('name')->get(['id', 'name', 'code']),
            'rooms' => Room::active()->orderBy('name')->get(),
            'days' => TimetableSlot::DAYS,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', TimetableSlot::class);

        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'instructor_id' => 'required|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
            'day_of_week' => 'required|in:' . implode(',', TimetableSlot::DAYS),
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'recurrence' => 'required|in:weekly,biweekly,once',
        ]);

        $slot = TimetableSlot::create($data);

        $this->audit->logCreated($slot);

        return redirect()->route('hive.timetable.index')->with('success', 'Timetable slot created.');
    }

    public function edit(TimetableSlot $slot)
    {
        $this->authorize('update', $slot);

        return Inertia::render('Hive/Timetable/Edit', [
            'slot' => $slot->load(['module', 'instructor', 'room']),
            'modules' => Module::orderBy('name')->get(['id', 'name', 'code']),
            'rooms' => Room::active()->orderBy('name')->get(),
            'days' => TimetableSlot::DAYS,
        ]);
    }

    public function update(Request $request, TimetableSlot $slot)
    {
        $this->authorize('update', $slot);

        $data = $request->validate([
            'module_id' => 'sometimes|exists:modules,id',
            'instructor_id' => 'sometimes|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
            'day_of_week' => 'sometimes|in:' . implode(',', TimetableSlot::DAYS),
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'semester' => 'sometimes|string',
            'academic_year' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'recurrence' => 'sometimes|in:weekly,biweekly,once',
            'is_active' => 'sometimes|boolean',
        ]);

        $oldValues = $slot->getOriginal();
        $slot->update($data);

        $this->audit->logUpdated($slot, $oldValues);

        return redirect()->route('hive.timetable.index')->with('success', 'Timetable slot updated.');
    }

    public function destroy(TimetableSlot $slot)
    {
        $this->authorize('delete', $slot);

        $this->audit->logDeleted($slot);

        $slot->delete();

        return redirect()->route('hive.timetable.index')->with('success', 'Timetable slot deleted.');
    }
}
