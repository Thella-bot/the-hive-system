<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;

use App\Actions\Hive\CreateNewStaff;
use App\Actions\Hive\UpdateStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $staff = User::role(['academic_staff', 'non_academic_staff'])->with('roles')->get();
        return Inertia::render('Hive/Staff/Index', [
            'staff' => $staff,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::whereIn('name', ['academic_staff', 'non_academic_staff'])->get();
        return Inertia::render('Hive/Staff/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateNewStaff $creator)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $creator->create($request->all());

        return redirect()->route('hive.staff.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $staff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $staff)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $roles = Role::whereIn('name', ['academic_staff', 'non_academic_staff'])->get();
        $staff->load('roles');
        return Inertia::render('Hive/Staff/Edit', [
            'managedStaff' => $staff,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $staff, UpdateStaff $updater)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $updater->update($staff, $request->all());

        return redirect()->route('hive.staff.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $staff)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $staff->delete();

        return redirect()->route('hive.staff.index');
    }
}