<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    public function scan()
    {
        $this->authorize('viewAny', Attendance::class);

        return inertia('Hive/Attendance/Scan');
    }

    public function checkin(Request $request)
    {
        $this->authorize('checkin', Attendance::class);

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'method' => 'nullable|in:qr,manual',
        ]);

        $code = strip_tags($validated['code']);

        // Parse code format: EVENT-{id}
        if (str_starts_with($code, 'EVENT-')) {
            $eventId = (int) substr($code, 6);
            $event = Event::find($eventId);

            if (!$event) {
                Log::warning('Attendance check-in failed: event not found', [
                    'user_id' => auth()->id(),
                    'code' => $code,
                    'ip' => $request->ip(),
                ]);
                return back()->withErrors(['code' => 'Event not found.']);
            }

            // Validate event is active and within valid date range
            if ($event->is_active === false) {
                Log::warning('Attendance check-in failed: event not active', [
                    'user_id' => auth()->id(),
                    'event_id' => $eventId,
                ]);
                return back()->withErrors(['code' => 'Event is not active.']);
            }

            $existing = Attendance::where('user_id', auth()->id())
                ->where('event_id', $eventId)
                ->first();

            if ($existing) {
                return back()->with('info', 'Already checked in for ' . $event->title);
            }

            DB::transaction(function () use ($eventId, $validated) {
                $attendance = Attendance::create([
                    'user_id' => auth()->id(),
                    'event_id' => $eventId,
                    'checked_in_at' => now(),
                    'method' => $validated['method'] ?? 'qr',
                ]);

                // Log audit trail
                $this->audit->logCreated($attendance);
            });

            return back()->with('success', 'Checked in for ' . $event->title);
        }

        Log::warning('Attendance check-in failed: invalid code format', [
            'user_id' => auth()->id(),
            'code' => $code,
            'ip' => $request->ip(),
        ]);

        return back()->withErrors(['code' => 'Invalid QR code.']);
    }
}
