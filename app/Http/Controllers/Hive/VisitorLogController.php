<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use App\Models\User;
use App\Notifications\VisitorArrivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class VisitorLogController extends Controller
{
    public function index()
    {
        $logs = VisitorLog::with('host')
            ->when(!auth()->user()->hasAnyRole(['super-admin', 'school-admin']), function ($q) {
                $q->where('host_user_id', auth()->id());
            })
            ->latest()
            ->paginate(30);

        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);
        return Inertia::render('Hive/VisitorLogs/Index', ['logs' => $logs, 'users' => $users]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:50',
            'host_user_id' => 'nullable|exists:users,id',
            'purpose' => 'nullable|string|max:255',
            'arrived_at' => 'required|date',
        ]);

        $log = VisitorLog::create($data);

        if ($log->host_user_id) {
            $host = User::find($log->host_user_id);
            if ($host) {
                Notification::send($host, new VisitorArrivedNotification($log));
            }
        }

        return back()->with('success', 'Visitor checked in.');
    }

    public function checkOut(VisitorLog $log)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['super-admin', 'school-admin']) && $log->host_user_id !== $user->id) {
            abort(403);
        }

        $log->update(['departed_at' => now(), 'status' => 'checked_out']);
        return back()->with('success', 'Visitor checked out.');
    }
}
