<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Module;
use App\Models\Programme;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Module::class);

        $user = $request->user();
        $query = Module::with(['department', 'programmes']);

        if ($user->isStudent()) {
            $query->whereIn('id', $user->modules()->pluck('id'));
        } elseif ($user->hasRole('academic_staff')) {
            $query->whereIn('id', $user->instructedModules()->pluck('id'));
        }

        $modules = $query->paginate(15);

        return Inertia::render('Hive/Modules/Index', ['modules' => $modules]);
    }

    public function create()
    {
        $this->authorize('create', Module::class);
        $departments = Department::all();
        $programmes = Programme::all();
        return Inertia::render('Hive/Modules/Create', [
            'departments' => $departments,
            'programmes' => $programmes,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Module::class);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:modules',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:1',
            'department_id' => 'required|exists:departments,id',
            'programme_id' => 'required|exists:programmes,id',
        ]);
        Module::create($data);
        return redirect()->route('hive.modules.index')->with('success', 'Module created successfully.');
    }

    public function show(Module $module)
    {
        $this->authorize('view', $module);
        $module->load(['department', 'programme', 'instructors']);
        return Inertia::render('Hive/Modules/Show', [
            'module' => $module,
        ]);
    }

    public function edit(Module $module)
    {
        $this->authorize('update', $module);
        $departments = Department::all();
        $programmes = Programme::all();
        return Inertia::render('Hive/Modules/Edit', [
            'module' => $module,
            'departments' => $departments,
            'programmes' => $programmes,
        ]);
    }

    public function update(Request $request, Module $module)
    {
        $this->authorize('update', $module);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:modules,code,' . $module->id,
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:1',
            'department_id' => 'required|exists:departments,id',
            'programme_id' => 'required|exists:programmes,id',
        ]);
        $module->update($data);
        return redirect()->route('hive.modules.index')->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module)
    {
        $this->authorize('delete', $module);
        $module->delete();
        return redirect()->route('hive.modules.index')->with('success', 'Module deleted successfully.');
    }

    public function storeProgramme(Request $request)
    {
        $this->authorize('create', Module::class);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:255',
            'duration_months' => 'nullable|integer|min:1',
            'total_price' => 'nullable|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',
            'registration_fee' => 'nullable|numeric|min:0',
            'academic_resource_fee' => 'nullable|numeric|min:0',
            'uniform_fee' => 'nullable|numeric|min:0',
            'tools_cost' => 'nullable|numeric|min:0',
            'requirements' => 'nullable|string',
            'payment_method' => 'nullable|string|max:255',
            'intake_period' => 'nullable|string|max:255',
            'career_opportunities' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
        ]);
        Programme::create($data);
        return redirect()->route('hive.programmes.index')->with('success', 'Programme created successfully.');
    }
}