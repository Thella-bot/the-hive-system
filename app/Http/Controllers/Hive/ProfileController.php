<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOwnProfileRequest;
use App\Models\Cohort;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'user'                 => $user,
            'profile'              => $user->profile,
            'departments'          => Department::active()->select('id', 'name')->get(),
            'cohorts'              => Cohort::active()->with('department:id,name')->select('id', 'name', 'department_id')->get(),
        ]);
    }

    public function update(UpdateOwnProfileRequest $request)
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate();
        $this->authorize('update', $profile);

        $data = $request->validated();

        $userFields = [];
        if (array_key_exists('gender', $data)) {
            $userFields['gender'] = $data['gender'];
        }
        if (array_key_exists('national_id_number', $data)) {
            $userFields['national_id_number'] = $data['national_id_number'];
        }

        if ($userFields) {
            $user->update($userFields);
        }

        $profileFields = $data;
        unset($profileFields['gender'], $profileFields['national_id_number'], $profileFields['profile_picture']);

        if ($request->hasFile('profile_picture')) {
            if ($profile->profile_picture_path) {
                Storage::disk('public')->delete($profile->profile_picture_path);
            }
            $profileFields['profile_picture_path'] = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        $profile->update($profileFields);

        return back()->with('success', 'Profile updated.');
    }
}
