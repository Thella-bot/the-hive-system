<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Attendance::class, 'attendance');
    }

    public function scan()
    {
        return inertia('Hive/Attendance/Scan');
    }

    public function checkin(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'method' => 'nullable|in:qr,manual',
        ]);

        $code = $validated['code'];

        // Parse code format: EVENT-{id} or SLOT-{id}
        if (str_starts_with($code, 'EVENT-')) {
            $eventId = (int) substr($code, 6);
            $event = Event::find($eventId);
            if (!$event) return back()->withErrors(['code' => 'Event not found.']);

            DB::transaction(function () use ($eventId, $validated) {
                Attendance::firstOrCreate(
                    ['user_id' => auth()->id(), 'event_id' => $eventId],
                    ['checked_in_at' => now(), 'method' => $validated['method'] ?? 'qr']
                );
            });

            return back()->with('success', 'Checked in for ' . $event->title);
        }

        return back()->withErrors(['code' => 'Invalid QR code.']);
    }
}
