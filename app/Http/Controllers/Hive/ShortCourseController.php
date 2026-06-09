<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Programme;
use App\Models\ShortCourse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShortCourseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ShortCourse::class, 'short_course');
    }

    public function index(Request $request): Response
    {
        $departmentId = $request->query('departmentId');

        $query = ShortCourse::with('courseable');

        if ($departmentId) {
            $query->whereHasMorph('courseable', [Department::class], fn($q) => $q->where('id', $departmentId));
        }

        $shortCourses = $query->latest()->paginate(20);

        return Inertia::render('Hive/ShortCourses/Index', [
            'shortCourses' => fn () => $shortCourses,
            'departmentId' => $departmentId,
        ]);
    }

    public function create(Request $request): Response
    {
        $departmentId = $request->query('departmentId');

        $departments = Department::active()->orderBy('name')->get();
        $programmes = Programme::with('department')->get();

        return Inertia::render('Hive/ShortCourses/Create', [
            'departments' => $departments,
            'programmes' => $programmes,
            'departmentId' => $departmentId ? (int) $departmentId : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:workshop,training,short-course',
            'duration' => 'required|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'accepting_applications' => 'boolean',
            'courseable_type' => 'required|string|in:App\\Models\\Department,App\\Models\\Programme',
            'courseable_id' => 'required|integer',
        ]);

        ShortCourse::create($data);

        return redirect()->route('hive.short-courses.index')
            ->with('success', 'Short course created successfully.');
    }

    public function edit(ShortCourse $shortCourse): Response
    {
        $shortCourse->load('courseable');
        $departments = Department::active()->orderBy('name')->get();
        $programmes = Programme::with('department')->get();

        return Inertia::render('Hive/ShortCourses/Edit', [
            'shortCourse' => $shortCourse,
            'departments' => $departments,
            'programmes' => $programmes,
        ]);
    }

    public function update(Request $request, ShortCourse $shortCourse): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:workshop,training,short-course',
            'duration' => 'required|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'accepting_applications' => 'boolean',
            'courseable_type' => 'required|string|in:App\\Models\\Department,App\\Models\\Programme',
            'courseable_id' => 'required|integer',
        ]);

        $shortCourse->update($data);

        return redirect()->route('hive.short-courses.edit', $shortCourse)
            ->with('success', 'Short course updated successfully.');
    }

    public function destroy(ShortCourse $shortCourse): RedirectResponse
    {
        $shortCourse->delete();

        return redirect()->route('hive.short-courses.index')
            ->with('success', 'Short course deleted.');
    }
}
