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
        $this->authorize('approveUsers', User::class);

        $unapproved = User::role('unapproved')->get();

        return Inertia::render('Hive/Admin/ApproveUsers', [
            'unapprovedUsers' => $unapproved,
        ]);
    }

    public function approve(User $user, Request $request)
    {
        $this->authorize('approveUsers', User::class);
        $request->validate([
            'role' => 'required|in:student,super-admin,it-support,academic-director,program-coordinator,chef-instructor,pastry-instructor,sous-chef,admissions-officer,examination-cell,registrar,finance,procurement-manager,storekeeper,hr-manager,librarian,career-services,events-pr-manager,cafeteria-manager,parent-guardian,alumni',
        ]);

        $user->syncRoles([$request->role]);
        $user->approved_at = now();
        $user->save();

        $user->notify(new UserApproved());

        return back()->with('success', 'User approved.');
    }
}
