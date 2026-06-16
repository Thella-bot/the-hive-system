<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestSubmitted;
use App\Notifications\LeaveRequestUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(LeaveRequest::class, 'leave');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $leaves = LeaveRequest::query()
            ->with('user')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('Hive/Leaves/Index', [
            'leaves' => $leaves,
            'balance' => $user->profile?->leave_balance ?? 0,
        ]);
    }

    public function create()
    {
        return Inertia::render('Hive/Leaves/Create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $this->authorize('create', LeaveRequest::class);

        $data = $request->validate([
            'type' => 'required|in:annual,sick,maternity,paternity,compassionate,study,other',
            'half_day' => 'nullable|boolean',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $profile = $user->profile;

        // Check balance (if annual leave)
        $leave = new LeaveRequest($data);
        if ($data['type'] === 'annual' && $profile && $profile->leave_balance < $leave->days()) {
            return back()->withErrors(['start_date' => 'Insufficient leave balance.']);
        }

        $leave = $user->leaveRequests()->create($data);

        $hrUsers = User::role(['super-admin', 'it-support', 'hr-manager'])->get();
        Notification::send($hrUsers, new LeaveRequestSubmitted($leave));

        return redirect()->route('hive.leaves.index')->with('success', 'Leave request submitted.');
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        $this->authorize('update', $leave);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $leave->update([
            'status' => $validated['status'],
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        if ($validated['status'] === 'approved') {
            $leave->deductFromBalance();
        } elseif ($validated['status'] === 'rejected' && !empty($validated['rejection_reason'])) {
            $leave->update(['rejection_reason' => $validated['rejection_reason']]);
        }

        $leave->user->notify(new LeaveRequestUpdated($leave));

        return back()->with('success', 'Leave request ' . $validated['status'] . '.');
    }

    public function destroy(Request $request, LeaveRequest $leave)
    {
        if ($leave->user_id !== $request->user()->id) {
            abort(403);
        }
        if ($leave->status !== 'pending' || $leave->is_cancelled) {
            abort(403, 'Only pending requests can be cancelled.');
        }

        $leave->update(['is_cancelled' => true, 'cancelled_at' => now()]);

        return back()->with('success', 'Leave request cancelled.');
    }
}
