<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\Module;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CourseMaterialController extends Controller
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    public function index(Request $request, Module $module)
    {
        $user = $request->user();
        $query = CourseMaterial::with('uploader')->forModule($module->id);

        if ($user->isStudent()) {
            $query->published();
        }

        if ($request->filled('category')) {
            $query->byCategory($request->input('category'));
        }

        $materials = $query->orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Hive/CourseMaterials/Index', [
            'module' => $module,
            'materials' => $materials,
            'categories' => CourseMaterial::CATEGORIES,
            'filters' => $request->only('category'),
        ]);
    }

    public function create(Module $module)
    {
        $this->authorize('create', [CourseMaterial::class, $module]);

        return Inertia::render('Hive/CourseMaterials/Create', [
            'module' => $module,
            'categories' => CourseMaterial::CATEGORIES,
        ]);
    }

    public function store(Request $request, Module $module)
    {
        $this->authorize('create', [CourseMaterial::class, $module]);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => 'required|file|max:51200',
            'category' => 'required|in:' . implode(',', array_keys(CourseMaterial::CATEGORIES)),
            'is_published' => 'boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store('private/course-materials/' . $module->id);

        $material = CourseMaterial::create([
            'module_id' => $module->id,
            'uploaded_by' => $request->user()->id,
            'title' => strip_tags($data['title']),
            'description' => isset($data['description']) ? strip_tags($data['description']) : null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'category' => $data['category'],
            'is_published' => $data['is_published'] ?? true,
        ]);

        $this->audit->logCreated($material);

        return redirect()->route('hive.modules.courses.index', $module)
            ->with('success', 'Course material uploaded.');
    }

    public function edit(Module $module, CourseMaterial $material)
    {
        $this->authorize('update', [$material, $module]);

        return Inertia::render('Hive/CourseMaterials/Edit', [
            'module' => $module,
            'material' => $material,
            'categories' => CourseMaterial::CATEGORIES,
        ]);
    }

    public function update(Request $request, Module $module, CourseMaterial $material)
    {
        $this->authorize('update', [$material, $module]);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'sometimes|in:' . implode(',', array_keys(CourseMaterial::CATEGORIES)),
            'is_published' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $data['title'] = isset($data['title']) ? strip_tags($data['title']) : $material->title;
        $data['description'] = isset($data['description']) ? strip_tags($data['description']) : $material->description;

        $oldValues = $material->getOriginal();
        $material->update($data);

        $this->audit->logUpdated($material, $oldValues);

        return redirect()->route('hive.modules.courses.index', $module)
            ->with('success', 'Course material updated.');
    }

    public function download(Module $module, CourseMaterial $material)
    {
        $this->authorize('view', [$material, $module]);

        return Storage::download($material->file_path, $material->file_name);
    }

    public function destroy(Module $module, CourseMaterial $material)
    {
        $this->authorize('delete', [$material, $module]);

        Storage::delete($material->file_path);

        $this->audit->logDeleted($material);

        $material->delete();

        return redirect()->route('hive.modules.courses.index', $module)
            ->with('success', 'Course material deleted.');
    }
}
