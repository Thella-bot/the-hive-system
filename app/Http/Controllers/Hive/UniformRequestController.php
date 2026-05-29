<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\UniformRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UniformRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $requests = UniformRequest::with('user', 'reviewer')
            ->when(!$user->hasAnyRole(['super-admin', 'school-admin']), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('Hive/UniformRequests/Index', ['requests' => $requests]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_type' => 'required|string|max:100',
            'size' => 'nullable|string|max:50',
            'quantity' => 'nullable|integer|min:1|max:10',
        ]);

        $data['user_id'] = auth()->id();
        UniformRequest::create($data);

        return back()->with('success', 'Uniform request submitted.');
    }

    public function review(Request $request, UniformRequest $request_)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,issued,rejected',
        ]);

        $request_->update([
            'status' => $validated['status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Request {$validated['status']}.");
    }
}
