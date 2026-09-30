<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,read,unread'],
        ]);

        $query = $request->user()->notifications()->latest();

        $this->applyTypeFilter($query, $validated['type'] ?? null);
        $this->applyStatusFilter($query, $validated['status'] ?? 'all');

        $notifications = $query->paginate(20)->withQueryString();

        return Inertia::render('Hive/Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
            'filters' => [
                'type' => $validated['type'] ?? null,
                'status' => $validated['status'] ?? 'all',
            ],
            'availableTypes' => $this->availableTypes($request),
        ]);
    }

    /**
     * JSON feed for the header dropdown. Returns the newest notifications plus
     * the current unread count so the bell badge can be refreshed on demand.
     */
    public function preview(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => $user->notifications()
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'message' => $notification->data['message']
                        ?? $notification->data['title']
                        ?? 'Notification',
                    'url' => $notification->data['url'] ?? null,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->toIso8601String(),
                ]),
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, $notification)
    {
        $request->user()->notifications()->whereKey($notification)->first()?->markAsRead();
        $this->forgetUnreadCount($request);

        return back();
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        $this->forgetUnreadCount($request);

        return back();
    }

    /**
     * Mark a specific set of notifications as read.
     */
    public function markSelectedRead(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $count = $request->user()->unreadNotifications()
            ->whereIn('id', $validated['ids'])
            ->get()
            ->markAsRead()
            ->count();

        $this->forgetUnreadCount($request);

        return back()->with('success', "{$count} notification(s) marked as read.");
    }

    /**
     * Delete a specific set of notifications.
     */
    public function destroySelected(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $count = $request->user()->notifications()
            ->whereIn('id', $validated['ids'])
            ->delete();

        $this->forgetUnreadCount($request);

        return back()->with('success', "{$count} notification(s) deleted.");
    }

    /**
     * The bell badge is served from a short-lived cache, so any mutation has to
     * clear it or the badge stays stale.
     */
    private function forgetUnreadCount(Request $request): void
    {
        if ($request->user()) {
            cache()->forget("notifications.unread.{$request->user()->id}");
        }
    }

    /**
     * The database stores notification types as class names, so the filter has
     * to match on a LIKE rather than an exact column comparison.
     */
    private function applyTypeFilter($query, ?string $type): void
    {
        if ($type === null || $type === '') {
            return;
        }

        // "SubmissionApproved" should also match a "submission_approved" snake
        // case payload, so normalise both sides to lowercase with no separators.
        $needle = strtolower(str_replace(['_', '-', '\\'], '', $type));

        $query->whereRaw('LOWER(REPLACE(REPLACE(type, ?, ?), ?, ?)) LIKE ?', [
            '_', '', '-', '', '%' . $needle . '%',
        ]);
    }

    private function applyStatusFilter($query, string $status): void
    {
        match ($status) {
            'read' => $query->whereNotNull('read_at'),
            'unread' => $query->whereNull('read_at'),
            default => null,
        };
    }

    /**
     * Distinct notification types this user actually has, for the filter chips.
     */
    private function availableTypes(Request $request): array
    {
        return $request->user()->notifications()
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->map(fn (string $type) => [
                'value' => class_basename($type),
                'label' => class_basename($type),
            ])
            ->values()
            ->all();
    }
}
