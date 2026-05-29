<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\ProgrammeWaitlist;
use App\Models\Programme;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WaitlistController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $waitlists = $user->isStudent()
            ? ProgrammeWaitlist::where('user_id', $user->id)->with('programme')->get()
            : ProgrammeWaitlist::with(['user', 'programme'])->latest()->paginate(20);

        return Inertia::render('Hive/Waitlists/Index', [
            'waitlists' => $waitlists,
            'programmes' => Programme::all(['id', 'name']),
        ]);
    }

    public function join(Request $request)
    {
        $data = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
        ]);

        $existing = ProgrammeWaitlist::where('programme_id', $data['programme_id'])
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return back()->withErrors(['programme_id' => 'You are already on this waitlist.']);
        }

        $position = ProgrammeWaitlist::where('programme_id', $data['programme_id'])->max('position') + 1;

        ProgrammeWaitlist::create([
            'programme_id' => $data['programme_id'],
            'user_id' => auth()->id(),
            'position' => $position,
            'joined_at' => now(),
        ]);

        return back()->with('success', "You have been added to the waitlist at position {$position}.");
    }

    public function leave(ProgrammeWaitlist $waitlist)
    {
        if ($waitlist->user_id !== auth()->id()) {
            abort(403);
        }
        $waitlist->delete();
        return back()->with('success', 'You have left the waitlist.');
    }
}
