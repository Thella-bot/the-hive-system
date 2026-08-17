<?php
declare(strict_types=1);

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\GeneratesDocumentPdfs;
use App\Http\Requests\Hive\StoreStaffRequest;
use App\Http\Requests\Hive\UpdateStaffRequest;
use App\Models\User;
use App\Models\Department;
use App\Actions\Hive\CreateNewStaff;
use App\Actions\Hive\UpdateStaff;
use App\Services\SignatoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Inertia\Inertia;

class StaffController extends Controller
{
    use GeneratesDocumentPdfs;

    public function __construct(protected SignatoryService $signatory)
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
            ->with(['roles', 'profile'])
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
    public function store(StoreStaffRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->syncRoles($validated['roles'] ?? []);

        $user->profile()->create([
            'employee_number' => $validated['employee_number'] ?? null,
            'department_id'   => $validated['department_id'] ?? null,
            'designation'     => $validated['designation'] ?? null,
            'specialization'  => $validated['specialization'] ?? null,
            'phone'           => $validated['phone'] ?? null,
            'date_of_birth'   => $validated['date_of_birth'] ?? null,
            'address'         => $validated['address'] ?? null,
        ]);

        return redirect()->route('hive.staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $staff)
    {
        $staff->load(['profile.department', 'roles']);
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
            'managedStaff' => $staff->load(['roles', 'profile.department']),
            'roles' => $roles,
            'departments' => Department::orderBy('name')->get(),
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStaffRequest $request, User $staff)
    {
        $this->authorize('update', $staff);

        $validated = $request->validated();

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

        if (!empty($validated['roles'])) {
            $staff->syncRoles($validated['roles']);
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
        $staff->load(['profile.department', 'roles']);

        $data = [
            'office' => config('institution.hr_office'),
            'ref' => config('institution.abbreviation') . '/HR/' . date('Y') . '/' . $staff->id,
            'date' => now(),
            'staff' => $staff,
            'position' => $request->position ?? $staff->profile->designation ?? 'Chef Instructor',
            'department' => $request->department ?? $staff->profile->department->name ?? 'Culinary Arts',
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
            'director_name' => $this->signatory->get('super-admin'),
        ];

        return $this->generatePdf('pdf.documents.staff_appointment', $data, 'Appointment_' . $staff->name . '.pdf', $staff->id);
    }

    /**
     * Generate Staff Warning Letter PDF.
     */
    public function generateWarning(User $staff, Request $request)
    {
        $staff->load(['profile.department']);

        $data = [
            'office' => config('institution.hr_office'),
            'ref' => config('institution.abbreviation') . '/HR/DSC/' . date('Y') . '/' . $staff->id,
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
            'hr_manager_name' => $this->signatory->get('hr-manager'),
        ];

        return $this->generatePdf('pdf.documents.staff_warning', $data, 'Staff_Warning_' . $staff->name . '.pdf', $staff->id);
    }
}
