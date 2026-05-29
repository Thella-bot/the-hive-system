<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Event::class, 'event');
    }

    public function index(): Response
    {
        $events = Event::orderByDesc('start')->paginate(15);

        return Inertia::render('Hive/Events/Index', [
            'events' => $events,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Hive/Events/Create', [
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('Hive/Events/Edit', [
            'event' => $event->load(['targetModules', 'rsvps.user']),
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function show(Event $event): Response
    {
        $userRsvp = $event->rsvpFor(auth()->user());
        return Inertia::render('Hive/Events/Edit', [
            'event' => $event->load(['targetModules', 'rsvps.user']),
            'userRsvp' => $userRsvp,
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'category' => 'nullable|string|max:50',
            'target_modules' => 'nullable|array',
            'target_modules.*' => 'exists:modules,id',
        ]);

        $event = Event::create($data);

        if (!empty($data['target_modules'])) {
            $event->targetModules()->attach($data['target_modules']);
        }

        return redirect()->route('hive.events.index')->with('success', 'Event created successfully.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'category' => 'nullable|string|max:50',
            'target_modules' => 'nullable|array',
            'target_modules.*' => 'exists:modules,id',
        ]);

        $event->update($data);

        if (isset($data['target_modules'])) {
            $event->targetModules()->sync($data['target_modules']);
        }

        return redirect()->route('hive.events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();
        return redirect()->route('hive.events.index')->with('success', 'Event deleted.');
    }

    public function rsvp(Request $request, Event $event)
    {
        $validated = $request->validate([
            'status' => 'required|in:attending,maybe,declined',
        ]);

        EventRsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => auth()->id()],
            ['status' => $validated['status']]
        );

        return back()->with('success', 'RSVP updated.');
    }

    public function exportICal(Event $event)
    {
        $content = $event->toICal();
        $filename = preg_replace('/[^a-z0-9]/i', '_', $event->title) . '.ics';

        return response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function qrCode(Event $event)
    {
        $code = "EVENT-{$event->id}";
        return response()->json(['code' => $code]);
    }
}