<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Event;
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

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $events = Event::orderByDesc('start')->paginate(15);

        return Inertia::render('Hive/Events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Hive/Events/Create', [
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('Hive/Events/Edit', [
            'event' => $event->load('targetModules'),
            'modules' => Module::all(['id', 'name', 'code']),
        ]);
    }

    public function show(Event $event): Response
    {
        return $this->edit($event);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('hive.events.index')->with('success', 'Event deleted.');
    }
}
