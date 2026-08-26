<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
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

        // Search functionality
        if ($request->filled('search')) {
            $search = strip_tags($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        // Filter by delivery mode
        if ($request->filled('delivery_mode')) {
            $query->where('delivery_mode', $request->input('delivery_mode'));
        }

        if ($user->isStudent()) {
            $query->whereIn('id', $user->modules()->pluck('id'));
        } elseif ($user->isFaculty()) {
            $query->whereIn('id', $user->instructedModules()->pluck('id'));
        }

        $modules = $query->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Hive/Modules/Index', [
            'modules' => $modules,
            'filters' => $request->only('search', 'department_id', 'delivery_mode'),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Module::class);
        $departments = Department::active()->orderBy('name')->get();
        $programmes = Programme::active()->orderBy('name')->get();
        return Inertia::render('Hive/Modules/Create', [
            'departments' => $departments,
            'programmes' => $programmes,
        ]);
    }

    public function store(StoreModuleRequest $request)
    {
        $this->authorize('create', Module::class);
        Module::create($request->validated());
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
        $departments = Department::active()->orderBy('name')->get();
        $programmes = Programme::active()->orderBy('name')->get();
        return Inertia::render('Hive/Modules/Edit', [
            'module' => $module,
            'departments' => $departments,
            'programmes' => $programmes,
        ]);
    }

    public function update(UpdateModuleRequest $request, Module $module)
    {
        $this->authorize('update', $module);
        $module->update($request->validated());
        return redirect()->route('hive.modules.index')->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module)
    {
        $this->authorize('delete', $module);
        $module->delete();
        return redirect()->route('hive.modules.index')->with('success', 'Module deleted successfully.');
    }

    /**
     * Duplicate an existing module.
     */
    public function duplicate(Module $module)
    {
        $this->authorize('create', Module::class);

        $newModule = $module->replicate();
        $newModule->name = $module->name . ' (Copy)';
        $newModule->code = $module->code . '-COPY-' . time();
        $newModule->save();

        // Sync programmes from original module
        $newModule->programmes()->sync($module->programmes->pluck('id'));

        return redirect()->route('hive.modules.edit', $newModule)
            ->with('success', 'Module duplicated successfully. You can now edit the duplicate.');
    }

    public function storeProgramme(Request $request)
    {
        $this->authorize('create', Module::class);
        $data = $request->validate([
            'code' => 'required|string|max:255|unique:programmes,code',
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
