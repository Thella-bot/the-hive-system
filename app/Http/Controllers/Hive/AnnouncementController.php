<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;

use App\Models\Announcement;
use App\Models\Module;
use App\Models\User;
use App\Notifications\NewAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->get();
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
            'announcement' => $announcement->load('targetModules'),
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string|max:50',
            'target_roles' => 'nullable|array',
            'target_modules' => 'nullable|array',
            'target_modules.*' => 'exists:modules,id',
            'is_pinned' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $announcement = Announcement::create($validated);

        if (!empty($validated['target_modules'])) {
            $announcement->targetModules()->attach($validated['target_modules']);
        }

        $usersToNotify = User::where('id', '!=', $request->user()->id)->get();
        Notification::send($usersToNotify, new NewAnnouncement($announcement));

        return redirect()->back()->with('success', 'Announcement created and users notified.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string|max:50',
            'target_roles' => 'nullable|array',
            'target_modules' => 'nullable|array',
            'target_modules.*' => 'exists:modules,id',
            'is_pinned' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $announcement->update($validated);

        if (isset($validated['target_modules'])) {
            $announcement->targetModules()->sync($validated['target_modules']);
        }

        return redirect()->back()->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->back()->with('success', 'Announcement deleted.');
    }
}