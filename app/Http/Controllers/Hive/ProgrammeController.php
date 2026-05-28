<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Module;
use App\Models\Programme;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgrammeController extends Controller
{
    public function index()
    {
        $programmes = Programme::with('department')->paginate(10);

        return Inertia::render('Hive/Programmes/Index', [
            'programmes' => $programmes,
        ]);
    }

    public function create()
    {
        return Inertia::render('Hive/Programmes/Create', [
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Programme::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        Programme::create($data);

        return redirect()->route('hive.programmes.index')->with('success', 'Programme created successfully.');
    }

    public function show(Programme $programme)
    {
        $programme->load(['department', 'modules']);

        return Inertia::render('Hive/Programmes/Show', [
            'programme' => $programme,
        ]);
    }

    public function edit(Programme $programme)
    {
        $allModules = Module::orderBy('name')->get();

        return Inertia::render('Hive/Programmes/Edit', [
            'programme' => $programme->load('modules'),
            'allModules' => $allModules,
        ]);
    }

    public function update(Request $request, Programme $programme)
    {
        $this->authorize('update', $programme);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|string|max:255',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $programme->update(array_diff_key($data, array_flip(['modules'])));

        if (array_key_exists('modules', $data)) {
            $programme->modules()->sync($data['modules']);
        }

        return redirect()->route('hive.programmes.show', $programme)->with('success', 'Programme updated successfully.');
    }

    public function destroy(Request $request, Programme $programme)
    {
        $this->authorize('delete', $programme);

        $programme->delete();

        return redirect()->route('hive.programmes.index')->with('success', 'Programme deleted successfully.');
    }
}
