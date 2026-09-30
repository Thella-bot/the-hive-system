<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Services\AuditService;
use App\Services\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function __construct(
        protected AuditService $audit,
        protected CsvExporter $csv,
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

    /**
     * The attendance register.
     *
     * A student only ever sees their own records. Staff need the seeded
     * `view-student-attendance` permission to see anyone's, and
     * `manage-student-attendance` to see the whole institute.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user->can('manage-student-attendance') || $user->can('view-student-attendance') || $user->isStudent(),
            403,
            'You are not allowed to view the attendance register.'
        );

        $query = $this->scopedQuery($request)->with(['user:id,name,email,student_number', 'event:id,title,start'])
            ->orderByDesc('checked_in_at');

        $this->applyFilters($query, $request);

        return inertia('Hive/Attendance/Index', [
            'records' => $query->paginate(25)->withQueryString(),
            'filters' => [
                'method' => $request->query('method'),
                'event_id' => $request->query('event_id'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'search' => $request->query('search'),
            ],
            'methods' => ['qr', 'manual'],
            'canViewAll' => $user->can('manage-student-attendance'),
            'canScan' => $user->can('checkin'),
        ]);
    }

    /**
     * Export the attendance register to CSV, honouring the same filters and
     * the same visibility rules as the listing.
     */
    public function export(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user->can('manage-student-attendance') || $user->can('view-student-attendance') || $user->isStudent(),
            403,
            'You are not allowed to export the attendance register.'
        );

        $records = $this->scopedQuery($request)
            ->with(['user:id,name,email,student_number', 'event:id,title,start'])
            ->orderByDesc('checked_in_at')
            ->get();

        $this->audit->log('exported', $user, [
            'resource' => 'attendance',
            'row_count' => $records->count(),
        ]);

        return $this->csv->download(
            $records->map(fn (Attendance $record) => [
                'student' => $record->user?->name,
                'student_number' => $record->user?->student_number,
                'email' => $record->user?->email,
                'event' => $record->event?->title,
                'event_type' => $record->event_type,
                'event_start' => $this->csv->date($record->event?->start, 'Y-m-d H:i'),
                'method' => $record->method,
                'checked_in_at' => $this->csv->date($record->checked_in_at, 'Y-m-d H:i'),
            ]),
            ['Student', 'Student Number', 'Email', 'Event', 'Event Type', 'Event Start', 'Method', 'Checked In'],
            'attendance'
        );
    }

    /**
     * Restrict the register to what the caller is allowed to see.
     */
    private function scopedQuery(Request $request)
    {
        $query = Attendance::query();
        $user = $request->user();

        if ($user->can('manage-student-attendance')) {
            return $query;
        }

        // Everyone else is limited to their own rows, even if they hold the
        // narrower view permission.
        return $query->where('attendances.user_id', $user->id);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('method')) {
            $query->where('method', $request->query('method'));
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->integer('event_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('checked_in_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('checked_in_at', '<=', $request->query('date_to'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('user', fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('student_number', 'like', "%{$search}%"));
        }
    }
}
