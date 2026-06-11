<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Actions\Hive\CreateNewStaff;
use App\Actions\Hive\UpdateStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $staff = User::role(['academic_staff', 'non_academic_staff'])->with('roles')->paginate(15);
        return Inertia::render('Hive/Staff/Index', [
            'staff' => $staff,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);
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
        $this->authorize('create', User::class);
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
        $this->authorize('update', $staff);
        $roles = Role::whereIn('name', ['academic_staff', 'non_academic_staff'])->get();
        $staff->load(['roles', 'profile', 'department']);
        $isAdmin = auth()->user()?->hasAnyRole(['super-admin', 'school-admin']);
        return Inertia::render('Hive/Staff/Edit', [
            'managedStaff' => $staff,
            'roles' => $roles,
            'departments' => \App\Models\Department::orderBy('name')->get(),
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $staff, UpdateStaff $updater)
    {
        $this->authorize('update', $staff);
        $updater->update($staff, $request->all());
        return redirect()->route('hive.staff.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $staff)
    {
        $this->authorize('delete', $staff);
        $staff->delete();
        return redirect()->route('hive.staff.index');
    }
}