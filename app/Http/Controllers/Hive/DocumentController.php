<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Document::class, 'document');
    }

    /**
     * Display module selection page for documents.
     */
    public function moduleSelect(): \Inertia\Response
    {
        $user = auth()->user();

        if ($user->hasRole('student')) {
            $modules = $user->modules()->with('programme')->get();
        } elseif ($user->hasRole('academic_staff')) {
            $modules = $user->instructedModules()->with('programme')->get();
        } else {
            $modules = Module::with('programme')->get();
        }

        return Inertia::render('Hive/Documents/ModuleSelect', [
            'modules' => $modules,
        ]);
    }

    // List documents the user is allowed to see
    public function index(Request $request)
    {
        $user = $request->user();
        $roleNames = $user->roles->pluck('name')->toArray();

        // Enforce DocumentPolicy visibility rules at the query level (avoid loading + filtering in PHP).
        $documentsQuery = Document::query()
            ->with('latestVersion', 'creator', 'module')
            ->where('is_published', true)
            ->where(function ($q) use ($roleNames) {
                // public to all Hive users
                $q->whereNull('visible_to_roles')
                  // role restricted
                  ->orWhere(function ($q) use ($roleNames) {
                      foreach ($roleNames as $roleName) {
                          $q->orWhereJsonContains('visible_to_roles', $roleName);
                      }
                  });
            });

        // Filter by module if provided
        if ($request->has('module_id')) {
            $documentsQuery->where('module_id', $request->module_id);
        }

        // Filter by category if provided
        if ($request->filled('category')) {
            $documentsQuery->where('category', $request->category);
        }

        // admin can always view (policy returns true if no restrictions, and grants create/update elsewhere).
        // For listing, the visibility query already covers unrestricted docs (visible_to_roles IS NULL).

        $documents = $documentsQuery->latest()->get();

        return Inertia::render('Hive/Documents/Index', [
            'documents' => $documents->values(),
            'categories' => Document::query()
                ->where('is_published', true)
                ->where(function ($q) use ($roleNames) {
                    $q->whereNull('visible_to_roles')
                      ->orWhere(function ($q) use ($roleNames) {
                          foreach ($roleNames as $roleName) {
                              $q->orWhereJsonContains('visible_to_roles', $roleName);
                          }
                      });
                })
                ->distinct()
                ->pluck('category'),
        ]);
    }



    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('student')) {
            $modules = collect(); // Students can't upload documents for specific modules
        } elseif ($user->hasRole('academic_staff')) {
            $modules = $user->instructedModules()->get();
        } else {
            $modules = Module::all();
        }

        return Inertia::render('Hive/Documents/Create', [
            'modules' => $modules,
        ]);
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'module_id' => 'nullable|exists:modules,id',
            'visible_to_roles' => 'nullable|array',
            'file' => 'required|file|max:20480', // 20 MB max
        ]);

        $document = Document::create([
            'title' => $attrs['title'],
            'description' => $attrs['description'] ?? '',
            'category' => $attrs['category'],
            'module_id' => $attrs['module_id'] ?? null,
            'visible_to_roles' => $attrs['visible_to_roles'] ?? null,
            'is_published' => true,
            'created_by' => $request->user()->id,
        ]);

        // Store first version
        $file = $request->file('file');
        $path = $file->store('private/documents/' . $document->id);
        $document->versions()->create([
            'version_number' => 1,
            'file_path' => $path,
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('hive.documents.index')->with('success', 'Document uploaded.');
    }

    // Show document details (list versions)
    public function show(Document $document)
    {
        $document->load('versions.uploader', 'creator');
        return Inertia::render('Hive/Documents/Show', ['document' => $document]);
    }

    // Upload a new version
    public function addVersion(Request $request, Document $document)
    {
        $this->authorize('update', $document);
        $request->validate(['file' => 'required|file|max:20480']);

        $latestVersion = $document->latestVersion;
        $newNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        $file = $request->file('file');
        $path = $file->store('private/documents/' . $document->id);

        $document->versions()->create([
            'version_number' => $newNumber,
            'file_path' => $path,
            'uploaded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'New version uploaded.');
    }

    // Download a specific version (or the latest)
    public function download(DocumentVersion $version)
    {
        $this->authorize('view', $version->document);
        return Storage::download($version->file_path);
    }
}