<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\Module;
use App\Models\User;
use App\Events\EmergencyAlert;
use App\Notifications\NewAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::visibleTo($request->user())
            ->latest()
            ->with('attachments')
            ->paginate(20);

        return Inertia::render('Hive/Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }

    public function create()
    {
        return Inertia::render('Hive/Announcements/Create', [
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Hive/Announcements/Edit', [
            'announcement' => $announcement->load(['targetModules', 'attachments']),
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'body_html' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'target_roles' => 'nullable|array',
            'target_modules' => 'nullable|array',
            'target_modules.*' => 'exists:modules,id',
            'is_pinned' => 'nullable|boolean',
            'priority' => 'nullable|in:normal,urgent,emergency',
            'expires_at' => 'nullable|date',
        ]);

        $announcement = Announcement::create($validated);

        if (!empty($validated['target_modules'])) {
            $announcement->targetModules()->attach($validated['target_modules']);
        }

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('announcements', 'public');
                $announcement->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'size' => $file->getSize(),
                    'uploaded_by' => $request->user()->id,
                ]);
            }
        }

        $usersToNotify = User::where('id', '!=', $request->user()->id)->get();
        Notification::send($usersToNotify, new NewAnnouncement($announcement));

        // Emergency broadcasts force a fullscreen modal on all clients
        if (($validated['priority'] ?? 'normal') === 'emergency') {
            broadcast(new EmergencyAlert($announcement))->toOthers();
        }

        return redirect()->back()->with('success', 'Announcement created and users notified.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'body_html' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'target_roles' => 'nullable|array',
            'target_modules' => 'nullable|array',
            'target_modules.*' => 'exists:modules,id',
            'is_pinned' => 'nullable|boolean',
            'priority' => 'nullable|in:normal,urgent,emergency',
            'expires_at' => 'nullable|date',
        ]);

        $announcement->update($validated);

        if (isset($validated['target_modules'])) {
            $announcement->targetModules()->sync($validated['target_modules']);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('announcements', 'public');
                $announcement->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'size' => $file->getSize(),
                    'uploaded_by' => $request->user()->id,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        // Delete attachment files
        foreach ($announcement->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        $announcement->delete();
        return redirect()->back()->with('success', 'Announcement deleted.');
    }

    public function downloadAttachment(AnnouncementAttachment $attachment)
    {
        return Storage::disk('public')->download($attachment->file_path, $attachment->name);
    }
}
