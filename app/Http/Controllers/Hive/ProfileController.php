<?php
declare(strict_types=1);

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Cohort;
use App\Models\Department;
use App\Services\ReferenceDataService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user()->load('profile');
        $this->authorize('view', $user->profile);

        return Inertia::render('Hive/Profile/Edit', [
            'profile'     => $user->profile,
            'departments' => app(ReferenceDataService::class)->departments(),
            'cohorts'     => Cohort::active()->with('department:id,name')->select('id', 'name', 'department_id')->get(),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate();
        $this->authorize('update', $profile);

        $profile->update($request->validated());
        return back()->with('success', 'Profile updated.');
    }
}
