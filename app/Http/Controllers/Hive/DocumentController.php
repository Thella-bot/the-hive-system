<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
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
        } elseif ($user->isFaculty()) {
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
        $roleNames = collect($user->roles)->pluck('name')->toArray();
        $isStudent = $user->hasRole('student');
        $isStaff = $user->isStaff();
        $isAdmin = $user->isAdmin();

        // Enforce DocumentPolicy visibility rules at the query level (avoid loading + filtering in PHP).
        $documentsQuery = Document::query()
            ->with('latestVersion', 'creator', 'module')
            ->where('is_published', true)
            ->where(function ($q) use ($roleNames, $user, $isStudent, $isStaff) {
                // Check audience visibility
                $q->where(function ($q) use ($user, $isStudent, $isStaff, $roleNames) {
                    // Everyone (public)
                    $q->where('audience', 'everyone');

                    // All authenticated users
                    $q->orWhere('audience', 'all_users');

                    // Staff only
                    if ($isStaff) {
                        $q->orWhere('audience', 'staff_only');
                    }

                    // Students only or module students
                    if ($isStudent || $isStaff) {
                        $q->orWhere('audience', 'student_only');
                        $q->orWhere('audience', 'module_students');
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

        $documents = $documentsQuery->latest()->get();

        // Add acknowledgement counts
        $documents->each(function ($doc) {
            $doc->acknowledgements_count = $doc->acknowledgements()->count();
            $doc->is_acknowledged = $doc->isAcknowledgedBy(auth()->user());
        });

        // Get modules for filtering (staff/admin only)
        $modules = $isAdmin ? Module::with('programme')->get() : ($isStaff ? $user->instructedModules()->with('programme')->get() : collect());

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
            'modules' => $modules,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('student')) {
            $modules = collect(); // Students can't upload documents for specific modules
        } elseif ($user->isFaculty()) {
            $modules = $user->instructedModules()->get();
        } else {
            $modules = Module::with('programme')->get();
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
            'audience' => 'required|string|in:module_students,student_only,staff_only,all_users,everyone',
            'module_id' => 'required|exists:modules,id',
            'visible_to_roles' => 'nullable|array',
            'file' => 'required|file|max:20480', // 20 MB max
        ]);

        $document = Document::create([
            'title' => $attrs['title'],
            'description' => $attrs['description'] ?? '',
            'category' => $attrs['category'],
            'audience' => $attrs['audience'],
            'module_id' => $attrs['module_id'],
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
        $this->authorize('view', $document);
        $document->load('versions.uploader', 'creator');
        $document->is_acknowledged = $document->isAcknowledgedBy(auth()->user());
        $document->acknowledgements_count = $document->acknowledgements()->count();
        return Inertia::render('Hive/Documents/Show', ['document' => $document]);
    }

    // Edit form
    public function edit(Document $document)
    {
        $this->authorize('update', $document);
        $user = auth()->user();

        if ($user->isFaculty()) {
            $modules = $user->instructedModules()->get();
        } else {
            $modules = Module::with('programme')->get();
        }

        return Inertia::render('Hive/Documents/Edit', [
            'document' => $document,
            'modules' => $modules,
        ]);
    }

    // Update document metadata
    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'audience' => 'required|string|in:module_students,student_only,staff_only,all_users,everyone',
            'module_id' => 'nullable|exists:modules,id',
            'visible_to_roles' => 'nullable|array',
            'is_published' => 'nullable|boolean',
        ]);

        $document->update($validated);

        return redirect()->route('hive.documents.show', $document->id)
            ->with('success', 'Document updated.');
    }

    // Delete document
    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        // Delete all version files
        foreach ($document->versions as $version) {
            Storage::delete($version->file_path);
        }

        $document->delete();

        return redirect()->route('hive.documents.index')
            ->with('success', 'Document deleted.');
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
        $filename = ($version->document->title ?: 'document') . '.pdf';
        return Storage::download($version->file_path, $filename);
    }

    // Mark document as acknowledged/read
    public function acknowledge(Request $request, Document $document)
    {
        $this->authorize('view', $document);

        $document->acknowledgements()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['acknowledged_at' => now()]
        );

        return back()->with('success', 'Document acknowledged.');
    }

    // View acknowledgement stats (admin only)
    public function acknowledgements(Document $document)
    {
        $this->authorize('view', $document);

        $acknowledgements = $document->acknowledgements()
            ->with('user')
            ->orderBy('acknowledged_at', 'desc')
            ->get();

        return Inertia::render('Hive/Documents/Acknowledgements', [
            'document' => $document,
            'acknowledgements' => $acknowledgements,
        ]);
    }
}