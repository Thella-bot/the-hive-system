<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Actions\Hive\CreateNewStaff;
use App\Actions\Hive\UpdateStaff;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'staff');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $staff = User::role(['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager'])
            ->with('roles')
            ->paginate(15);

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
        $roles = Role::whereIn('name', ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager'])->get();
        return Inertia::render('Hive/Staff/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'role' => 'nullable|string|exists:roles,name',
            'employee_number' => 'nullable|string|unique:users,employee_number',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'employee_number' => $validated['employee_number'] ?? 'EMP-' . date('Y') . '-' . rand(1000, 9999),
        ]);

        if (!empty($validated['role_id'])) {
            $role = Role::findById($validated['role_id']);
            $user->assignRole($role);
        } elseif (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return redirect()->route('hive.staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $staff)
    {
        $staff->load(['profile', 'roles']);
        return Inertia::render('Hive/Staff/Show', [
            'staff' => $staff,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $staff)
    {
        $this->authorize('update', $staff);
        $roles = Role::whereIn('name', ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager'])->get();
        $isAdmin = auth()->user()?->isAdmin();
        return Inertia::render('Hive/Staff/Edit', [
            'managedStaff' => $staff->load(['roles', 'profile', 'department']),
            'roles' => $roles,
            'departments' => Department::orderBy('name')->get(),
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $staff)
    {
        $this->authorize('update', $staff);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'role' => 'nullable|string|exists:roles,name',
            'employee_number' => 'nullable|string|unique:users,employee_number,' . $staff->id,
            'department_id' => 'nullable|exists:departments,id',
            'designation' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'nullable|date',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (isset($validated['password'])) {
            $staff->update(['password' => $validated['password']]);
        }

        if (!empty($validated['role'])) {
            $staff->syncRoles([$validated['role']]);
        }

        $staff->profile()->updateOrCreate(
            ['profileable_id' => $staff->id, 'profileable_type' => User::class],
            array_filter([
                'employee_number' => $validated['employee_number'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'hire_date' => $validated['hire_date'] ?? null,
            ])
        );

        return redirect()->route('hive.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $staff)
    {
        $this->authorize('delete', $staff);
        $staff->delete();
        return redirect()->route('hive.staff.index')
            ->with('success', 'Staff member deleted successfully.');
    }

    // ---------- PDF GENERATION ----------

    /**
     * Generate Staff Appointment Letter PDF.
     */
    public function generateAppointment(User $staff, Request $request)
    {
        $data = [
            'office' => 'Human Resources',
            'ref' => 'HBCI/HR/' . date('Y') . '/' . $staff->id,
            'date' => now(),
            'staff' => $staff,
            'position' => $request->position ?? $staff->position ?? 'Chef Instructor',
            'department' => $request->department ?? $staff->department ?? 'Culinary Arts',
            'contract_type' => $request->contract_type ?? 'Permanent',
            'contract_start' => Carbon::parse($request->contract_start ?? now()),
            'contract_end' => $request->contract_end ? Carbon::parse($request->contract_end) : null,
            'commencement_date' => Carbon::parse($request->commencement_date ?? now()),
            'reporting_to' => $request->reporting_to ?? 'Head of Department',
            'salary' => $request->salary ?? 15000.00,
            'probation_period' => $request->probation_period ?? '3 Months',
            'pay_day' => $request->pay_day ?? '25th',
            'notice_period' => $request->notice_period ?? '1 Month',
            'acceptance_deadline' => Carbon::parse($request->acceptance_deadline ?? now()->addDays(7)),
            'director_name' => $this->getSignatory('super-admin'),
        ];

        $pdf = Pdf::loadView('pdf.documents.staff_appointment', $data);
        return $pdf->stream('Appointment_' . $staff->name . '.pdf');
    }

    /**
     * Generate Staff Warning Letter PDF.
     */
    public function generateWarning(User $staff, Request $request)
    {
        $data = [
            'office' => 'Human Resources',
            'ref' => 'HBCI/HR/DSC/' . date('Y') . '/' . $staff->id,
            'date' => now(),
            'staff' => $staff,
            'warning_type' => $request->warning_type ?? 'First',
            'offence' => $request->offence ?? 'Policy Breach',
            'hearing_date' => Carbon::parse($request->hearing_date ?? now()),
            'hr_rep' => $request->hr_rep ?? 'HR Representative',
            'incident_description' => $request->incident_description ?? 'Description of incident...',
            'policy_violated' => $request->policy_violated ?? 'Employment Policy Section X',
            'expiry_date' => Carbon::parse($request->expiry_date ?? now()->addMonths(6)),
            'corrective_actions' => $request->corrective_actions ?? ['Attend training session'],
            'hr_manager_name' => $this->getSignatory('hr-manager'),
        ];

        $pdf = Pdf::loadView('pdf.documents.staff_warning', $data);
        return $pdf->stream('Staff_Warning_' . $staff->name . '.pdf');
    }

    private function getSignatory($role)
    {
        $user = User::role($role)->first();
        return $user ? $user->name : 'AUTHORISED SIGNATORY';
    }
}
