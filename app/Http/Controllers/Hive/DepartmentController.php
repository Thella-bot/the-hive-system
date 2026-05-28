<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Department::class, 'department');
    }

    public function index(): Response
    {
        $departments = Department::with('head')
            ->withCount(['cohorts'])
            ->withCount(['cohorts as active_cohort_count' => fn ($q) => $q->where('is_active', true)])
            ->addSelect(['staff_count' => User::role(['academic_staff', 'non_academic_staff'])
                ->join('profiles', 'users.id', '=', 'profiles.profileable_id')
                ->where('profiles.profileable_type', User::class)
                ->whereColumn('profiles.department_id', 'departments.id')
                ->select(DB::raw('count(*)'))
            ])
            ->latest()
            ->paginate(12);

        return Inertia::render('Hive/Departments/Index', [
            'departments' => fn () => $departments,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Hive/Departments/Create', [
            'eligibleHeads' => $this->eligibleHeads(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:departments,name',
            'description'  => 'nullable|string|max:2000',
            'head_user_id' => 'nullable|exists:users,id',
            'color'        => 'nullable|string|max:7',
            'is_active'    => 'boolean',
        ]);

        Department::create($data);

        return redirect()->route('hive.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department): Response
    {
        $department->load([
            'head',
            'staff.user',
            'cohorts' => fn ($q) => $q->with('academicYear')->withCount('students')->latest(),
        ]);

        $department->loadCount(['cohorts', 'staff']);

        return Inertia::render('Hive/Departments/Show', [
            'department' => $department,
        ]);
    }

    public function edit(Department $department): Response
    {
        return Inertia::render('Hive/Departments/Edit', [
            'department'     => $department,
            'eligibleHeads'  => $this->eligibleHeads(),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description'  => 'nullable|string|max:2000',
            'head_user_id' => 'nullable|exists:users,id',
            'color'        => 'nullable|string|max:7',
            'is_active'    => 'boolean',
        ]);

        $department->update($data);

        return redirect()->route('hive.departments.show', $department)
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('hive.departments.index')
            ->with('success', 'Department deleted.');
    }

    private function eligibleHeads()
    {
        return User::role(['super-admin', 'school-admin', 'department-head', 'chef-instructor'])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
    }
}