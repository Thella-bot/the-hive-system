<?php 

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\Profile;
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
            'departments' => Department::active()->select('id', 'name')->get(),
            'cohorts'     => Cohort::active()->with('department:id,name')->select('id', 'name', 'department_id')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate();
        $this->authorize('update', $profile);

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'dietary_restrictions' => 'nullable|string',

            // Staff fields
            'employee_number' => 'nullable|string|unique:profiles,employee_number,' . $profile->id,
            'department_id'   => 'nullable|exists:departments,id',
            'designation'     => 'nullable|string|max:255',
            'specialization'  => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'hire_date'       => 'nullable|date',
            'annual_leave_days' => 'nullable|integer',
            'leave_balance' => 'nullable|integer',

            // Student fields
            'student_number'             => 'nullable|string|unique:profiles,student_number,' . $profile->id,
            'cohort_id'                  => 'nullable|exists:cohorts,id',
            'enrollment_date'            => 'nullable|date',
            'expected_graduation_date'   => 'nullable|date|after_or_equal:enrollment_date',
            'graduation_date' => 'nullable|date|after_or_equal:enrollment_date',
            'status' => 'nullable|string',
        ]);

        $profile->update($data);
        return back()->with('success', 'Profile updated.');
    }
}