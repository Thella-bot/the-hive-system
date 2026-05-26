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
        // Policy scope would be better, but for now, this simplifies the if/else
        $leaves = LeaveRequest::query()
            ->with('user')
            ->when(!$user->hasAnyRole(['hr_staff', 'admin']), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->get();

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
            'type' => 'required|in:annual,sick,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        // Check balance (if annual leave)
        $days = (new \DateTime($data['start_date']))->diff(new \DateTime($data['end_date']))->days + 1;
        $profile = $user->profile;
        if ($data['type'] === 'annual' && $profile && $profile->leave_balance < $days) {
            return back()->withErrors(['start_date' => 'Insufficient leave balance.']);
        }

        $leave = $user->leaveRequests()->create($data);

        // Notify HR staff and admins
        $hrUsers = User::role(['hr_staff', 'admin'])->get();
        Notification::send($hrUsers, new LeaveRequestSubmitted($leave));

        return redirect()->route('hive.leaves.index')->with('success', 'Leave request submitted.');
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        $this->authorize('update', $leave);
        $request->validate(['status' => 'required|in:approved,rejected']);
        $leave->update([
            'status' => $request->status,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        // If approved, deduct balance
        if ($request->status === 'approved' && $leave->type === 'annual') {
            $profile = $leave->user->profile;
            if ($profile) {
                $days = $leave->days();
                $profile->decrement('leave_balance', min($days, $profile->leave_balance));
            }
        }

        // Notify the user who submitted the request
        $leave->user->notify(new LeaveRequestUpdated($leave));

        return back()->with('success', 'Leave request '.$request->status.'.');
    }
}
