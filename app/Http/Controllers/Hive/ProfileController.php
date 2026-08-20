<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOwnProfileRequest;
use App\Models\Cohort;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'profile.department',
            'profile.cohort.department',
            'roles',
            'programme',
        ]);

        return Inertia::render('Hive/Profile/Show', [
            'managedUser' => $user,
            'programme'   => $user->programme,
        ]);
    }

    public function edit(Request $request)
    {
        $user = $request->user()->load('profile');
        $this->authorize('view', $user->profile);

        return Inertia::render('Hive/Profile/Edit', [
            'profile'     => $user->profile,
            'departments' => Department::active()->select('id', 'name')->get(),
            'cohorts'     => Cohort::active()->with('department:id,name')->select('id', 'name', 'department_id')->get(),
        ]);
    }

    public function update(UpdateOwnProfileRequest $request)
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate();
        $this->authorize('update', $profile);

        $profile->update($request->validated());
        return back()->with('success', 'Profile updated.');
    }
}
