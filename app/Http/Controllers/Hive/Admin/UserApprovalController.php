<?php

namespace App\Http\Controllers\Hive\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserApproved;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserApprovalController extends Controller
{
    public function index()
    {
        $unapproved = User::role('unapproved')->get();
        return Inertia::render('Hive/Admin/ApproveUsers', [
            'unapprovedUsers' => $unapproved,
        ]);
    }

    public function approve(User $user, Request $request)
    {
        $request->validate([
            'role' => 'required|in:student,instructor,hr_staff',
        ]);

        $user->syncRoles([$request->role]);
        $user->approved_at = now();
        $user->save();

        $user->notify(new UserApproved());

        return back()->with('success', 'User approved.');
    }
}