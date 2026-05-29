<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AchievementController extends Controller
{
    public function index()
    {
        $achievements = Achievement::with('user')
            ->public()
            ->latest()
            ->paginate(20);

        $users = User::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Hive/Achievements/Index', [
            'achievements' => $achievements,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Achievement::class);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'awarded_by' => 'nullable|string|max:255',
            'awarded_at' => 'nullable|date',
            'photo_url' => 'nullable|string',
            'is_public' => 'nullable|boolean',
        ]);

        Achievement::create($data);

        return back()->with('success', 'Achievement added.');
    }

    public function destroy(Achievement $achievement)
    {
        $this->authorize('delete', $achievement);
        $achievement->delete();
        return back()->with('success', 'Achievement removed.');
    }
}
