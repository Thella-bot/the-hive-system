<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PollController extends Controller
{
    public function index()
    {
        $polls = Poll::with('user')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->paginate(20);

        $userId = auth()->id();
        $polls->each(function ($poll) use ($userId) {
            $poll->user_vote = $poll->votes()->where('user_id', $userId)->first();
            $poll->vote_counts = $poll->voteCounts();
        });

        return Inertia::render('Hive/Polls/Index', ['polls' => $polls]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'type' => 'required|in:binary,choice',
            'options' => 'nullable|array',
            'options.*' => 'string|max:100',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $data['user_id'] = $request->user()->id;
        $poll = Poll::create($data);

        return redirect()->back()->with('success', 'Poll created.');
    }

    public function vote(Request $request, Poll $poll)
    {
        if ($poll->expires_at && $poll->expires_at->isPast()) {
            return back()->withErrors(['poll' => 'This poll has expired.']);
        }

        if ($poll->hasVotedBy($request->user())) {
            return back()->withErrors(['poll' => 'You have already voted.']);
        }

        $choice = $request->validate(['choice' => 'required|string'])['choice'];
        $poll->votes()->create(['user_id' => $request->user()->id, 'choice' => $choice]);

        return back()->with('success', 'Vote recorded.');
    }

    public function destroy(Poll $poll)
    {
        $poll->votes()->delete();
        $poll->delete();
        return back()->with('success', 'Poll deleted.');
    }
}
