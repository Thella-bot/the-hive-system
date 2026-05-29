<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
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

        VisitorLog::create($data);
        return back()->with('success', 'Visitor checked in.');
    }

    public function checkOut(VisitorLog $log)
    {
        $log->update(['departed_at' => now(), 'status' => 'checked_out']);
        return back()->with('success', 'Visitor checked out.');
    }
}
