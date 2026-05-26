<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the users that need approval.
     *
     * @return \Inertia\Response
     */
    public function approve_users()
    {
        $unapprovedUsers = User::role('unapproved')->get();

        return Inertia::render('Hive/Admin/ApproveUsers', [
            'users' => $unapprovedUsers,
        ]);
    }
}