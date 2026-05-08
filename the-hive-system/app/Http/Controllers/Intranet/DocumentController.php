<?php namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Document::class, 'document');
    }

    // List documents the user is allowed to see
    public function index(Request $request)
    {
        $user = $request->user();
        $documents = Document::with('latestVersion', 'creator')
            ->where('is_published', true)
            ->get()
            ->filter(fn($doc) => $user->can('view', $doc)); // policy filter

        return Inertia::render('Intranet/Documents/Index', [
            'documents' => $documents->values(),
            'categories' => Document::distinct()->pluck('category'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Intranet/Documents/Create');
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'visible_to_roles' => 'nullable|array',
            'file' => 'required|file|max:20480', // 20 MB max
        ]);

        $document = Document::create([
            'title' => $attrs['title'],
            'description' => $attrs['description'] ?? '',
            'category' => $attrs['category'],
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

        return redirect()->route('intranet.documents.index')->with('success', 'Document uploaded.');
    }

    // Show document details (list versions)
    public function show(Document $document)
    {
        $document->load('versions.uploader', 'creator');
        return Inertia::render('Intranet/Documents/Show', ['document' => $document]);
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
