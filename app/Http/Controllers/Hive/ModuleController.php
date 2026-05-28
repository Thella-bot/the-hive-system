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
    public function index()
    {
        $this->authorize('viewAny', Module::class);
        $modules = Module::with('department', 'programme')->get();
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
}