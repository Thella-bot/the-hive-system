<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\NewAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $announcement = Announcement::create($validated);

        $usersToNotify = User::where('id', '!=', $request->user()->id)->get();
        Notification::send($usersToNotify, new NewAnnouncement($announcement));

        return redirect()->back()->with('success', 'Announcement created and users notified.');
    }
}
