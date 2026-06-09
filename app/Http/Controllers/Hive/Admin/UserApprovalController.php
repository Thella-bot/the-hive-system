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
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $unapproved = User::role('unapproved')->get();
        return Inertia::render('Hive/Admin/ApproveUsers', [
            'unapprovedUsers' => $unapproved,
        ]);
    }

    public function approve(User $user, Request $request)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $request->validate([
            'role' => 'required|in:student,academic_staff,non_academic_staff,department-head,chef-instructor',
        ]);

        $user->syncRoles([$request->role]);
        $user->approved_at = now();
        $user->save();

        $user->notify(new UserApproved());

        return back()->with('success', 'User approved.');
    }
}