<?php namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Announcement::class, 'announcement');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $announcements = Announcement::with('creator')
            ->visibleTo($user)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn($ann) => $user->can('view', $ann)); // double-check

        return Inertia::render('Intranet/Announcements/Index', [
            'announcements' => $announcements->values(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Intranet/Announcements/Create');
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'required|string',
            'target_roles' => 'nullable|array',
            'is_pinned' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        Announcement::create([
            ...$attrs,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('intranet.announcements.index')->with('success', 'Announcement created.');
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Intranet/Announcements/Edit', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $attrs = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'required|string',
            'target_roles' => 'nullable|array',
            'is_pinned' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        $announcement->update($attrs);
        return redirect()->route('intranet.announcements.index')->with('success', 'Updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Announcement removed.');
    }
}
