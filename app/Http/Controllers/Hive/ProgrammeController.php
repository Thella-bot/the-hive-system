<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Module;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProgrammeController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Programme::class);

        $programmes = Programme::with('department')->paginate(10);

        return Inertia::render('Hive/Programmes/Index', [
            'programmes' => $programmes,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Programme::class);

        return Inertia::render('Hive/Programmes/Create', [
            'departments' => Department::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Programme::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|string|max:255',
            'delivery_mode' => 'required|string|in:in_person,online,hybrid',
            'meeting_platform' => 'nullable|string|max:100',
            'meeting_link' => 'nullable|url|max:500',
            'location' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Sanitize inputs
        $data['name'] = strip_tags($data['name']);
        $data['description'] = strip_tags($data['description']);
        $data['location'] = strip_tags($data['location']);

        Programme::create($data);

        return redirect()->route('hive.programmes.index')->with('success', 'Programme created successfully.');
    }

    public function show(Programme $programme)
    {
        $this->authorize('view', $programme);

        $programme->load(['department', 'modules']);

        return Inertia::render('Hive/Programmes/Show', [
            'programme' => $programme,
        ]);
    }

    public function edit(Programme $programme)
    {
        $this->authorize('update', $programme);

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
            'delivery_mode' => 'required|string|in:in_person,online,hybrid',
            'meeting_platform' => 'nullable|string|max:100',
            'meeting_link' => 'nullable|url|max:500',
            'location' => 'nullable|string|max:255',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        // Sanitize inputs
        $data['name'] = strip_tags($data['name']);
        $data['description'] = strip_tags($data['description']);
        $data['location'] = strip_tags($data['location']);

        try {
            DB::transaction(function () use ($programme, $data) {
                $programme->update(array_diff_key($data, array_flip(['modules'])));

                if (array_key_exists('modules', $data)) {
                    $programme->modules()->sync($data['modules']);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update programme. Please try again.')
                ->withInput();
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
