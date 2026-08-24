<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\Module;
use App\Models\User;
use App\Events\EmergencyAlert;
use App\Notifications\NewAnnouncement;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

    public function store(StoreAnnouncementRequest $request)
    {
        $validated = $request->validated();

        $validated['created_by'] = $request->user()->id;

        $announcement = Announcement::create($validated);

        if (!empty($validated['target_modules'])) {
            $announcement->targetModules()->attach($validated['target_modules']);
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

        $usersToNotify = User::where('id', '!=', $request->user()->id)->get();
        Notification::send($usersToNotify, new NewAnnouncement($announcement));

        if (($validated['priority'] ?? 'normal') === 'emergency') {
            broadcast(new EmergencyAlert($announcement))->toOthers();
        }

        return redirect()->route('hive.announcements.index')->with('success', 'Announcement created and users notified.');
    }

    public function show(Request $request, Announcement $announcement)
    {
        $announcement = Announcement::visibleTo($request->user())
            ->where('id', $announcement->id)
            ->with(['attachments', 'targetModules'])
            ->firstOrFail();

        return Inertia::render('Hive/Announcements/Show', [
            'announcement' => $announcement,
        ]);
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Hive/Announcements/Edit', [
            'announcement' => $announcement->load(['targetModules', 'attachments']),
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $validated = $request->validated();

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

        return redirect()->route('hive.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        foreach ($announcement->attachments()->get() as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        $announcement->delete();
        return redirect()->route('hive.announcements.index')->with('success', 'Announcement deleted.');
    }

    public function downloadAttachment(Request $request, Announcement $announcement, AnnouncementAttachment $attachment)
    {
        $this->authorize('view', $announcement);

        if (!auth()->user()->isStaff()) {
            abort(403);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->name);
    }

    // ---------- PDF GENERATION ----------

    /**
     * Generate Internal Memo PDF.
     */
    public function generateMemo(Announcement $announcement)
    {
        $this->authorize('view', $announcement);

        $creator = $announcement->creator ?? User::find($announcement->created_by);

        $targetRoles = $announcement->target_roles ?? [];
        $recipients = is_array($targetRoles) && count($targetRoles) > 0
            ? implode(', ', $targetRoles)
            : 'All Staff';

        $data = [
            'office' => config('institution.academic_office'),
            'ref' => config('institution.abbreviation') . '/MEMO/' . date('Y') . '/' . $announcement->id,
            'date' => now(),
            'to' => $recipients,
            'from_name' => $creator->name ?? 'Administrator',
            'from_designation' => $creator->profile->designation ?? 'Administrator',
            'subject' => $announcement->title,
            'background' => $announcement->body,
            'key_points' => $announcement->body ? explode("\n", $announcement->body) : ['No key points provided.'],
            'action_required' => 'Please review the above information.',
            'contact_name' => $creator->name ?? 'Admin',
            'contact_email' => $creator->email ?? 'admin@hbci.ac.ls',
            'contact_ext' => '100',
            'cc' => $announcement->target_roles ? implode(', ', $targetRoles) : '',
        ];

        $pdf = Pdf::loadView('pdf.documents.internal_memo', $data);
        return $pdf->stream('Memo_' . $announcement->id . '.pdf');
    }
}
