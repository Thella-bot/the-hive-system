<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;

use App\Models\Cohort;
use App\Models\Department;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $paginatedUsers = User::with(['roles', 'profile'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn ($q) => $q->role($request->role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $users = $paginatedUsers->toArray();

        return Inertia::render('Hive/Users/Index', [
            'users'   => [
                'data' => $users['data'],
                'links' => $users['links'],
                'meta' => [
                    'current_page' => $users['current_page'],
                    'from' => $users['from'],
                    'last_page' => $users['last_page'],
                    'path' => $users['path'],
                    'per_page' => $users['per_page'],
                    'to' => $users['to'],
                    'total' => $users['total'],
                ],
            ],
            'roles'   => Role::orderBy('name')->pluck('name'),
            'filters' => $request->only('search', 'role'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Hive/Users/Create', [
            'roles'       => Role::orderBy('name')->get(['id', 'name']),
            'departments' => Department::active()->select('id', 'name')->get(),
            'cohorts'     => Cohort::active()->with('department:id,name')->select('id', 'name', 'department_id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',

            // Profile fields
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'annual_leave_days' => 'nullable|integer',
            'leave_balance' => 'nullable|integer',
            'employee_number' => 'nullable|string|unique:profiles,employee_number',
            'department_id'   => 'nullable|exists:departments,id',
            'designation'     => 'nullable|string|max:255',
            'specialization'  => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'hire_date'       => 'nullable|date',
            'student_number'             => 'nullable|string|unique:profiles,student_number',
            'cohort_id'                  => 'nullable|exists:cohorts,id',
            'enrollment_date'            => 'nullable|date',
            'expected_graduation_date'   => 'nullable|date|after:enrollment_date',
            'graduation_date' => 'nullable|date',
            'status' => 'nullable|string',
            'dietary_restrictions'       => 'nullable|array',
            'emergency_contact_relationship' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        $profileData = collect($data)->only((new Profile)->getFillable())->all();

        // Generate student/employee number
        $isStudent = $user->hasRole('student');
        $departmentId = null;

        if ($isStudent) {
            if (!empty($data['cohort_id'])) {
                $cohort = Cohort::find($data['cohort_id']);
                $departmentId = $cohort?->department_id;
            }
        } else {
            $departmentId = $data['department_id'] ?? null;
        }

        $number = $this->generateUniqueNumber($isStudent, $departmentId);

        if ($isStudent) {
            $profileData['student_number'] = $number;
        } else {
            $profileData['employee_number'] = $number;
        }

        $user->profile()->create($profileData);

        return redirect()->route('hive.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): Response
    {
        $user->load([
            'roles',
            'profile.department',
            'profile.cohort.department',
            'profile',
        ]);

        // Load applications (for applicants/students) - check if relationship exists
        $applications = method_exists($user, 'applications')
            ? $user->applications()->with(['programme', 'variant'])->latest()->limit(10)->get()
            : collect();

        // Load enrollments (for students) - check if relationship exists
        $enrollments = method_exists($user, 'enrollments')
            ? $user->enrollments()->with(['cohort', 'module'])->latest()->limit(10)->get()
            : collect();

        // Load certifications if relationship exists
        $certifications = method_exists($user, 'certifications')
            ? $user->certifications()->with(['module', 'awardedBy'])->latest()->limit(10)->get()
            : collect();

        // Count documents if relationship exists
        $documentCount = method_exists($user, 'documents')
            ? $user->documents()->count()
            : 0;

        // Get user's programme (for students)
        $programme = null;
        if ($user->profile && $user->profile->cohort_id) {
            $programme = \App\Models\Programme::whereHas('cohorts', fn($q) => $q->where('cohorts.id', $user->profile->cohort_id))->first();
        }

        return Inertia::render('Hive/Users/Show', [
            'managedUser' => $user,
            'applications' => $applications,
            'enrollments' => $enrollments,
            'certifications' => $certifications,
            'documentCount' => $documentCount,
            'programme' => $programme,
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load(['roles', 'profile']);
        $isAdmin = auth()->user()?->hasAnyRole(['super-admin', 'school-admin']);

        return Inertia::render('Hive/Users/Edit', [
            'managedUser'        => $user,
            'roles'       => Role::orderBy('name')->get(['id', 'name']),
            'departments' => Department::active()->select('id', 'name')->get(),
            'cohorts'     => Cohort::active()->with('department:id,name')->select('id', 'name', 'department_id')->get(),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            // Email is read-only - removed from validation to prevent changes
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',

            // Profile fields
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'annual_leave_days' => 'nullable|integer',
            'leave_balance' => 'nullable|integer',
            'department_id'   => 'nullable|exists:departments,id',
            'designation'     => 'nullable|string|max:255',
            'specialization'  => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'hire_date'       => 'nullable|date',
            'cohort_id'                  => 'nullable|exists:cohorts,id',
            'enrollment_date'            => 'nullable|date',
            'expected_graduation_date'   => 'nullable|date|after:enrollment_date',
            'graduation_date' => 'nullable|date',
            'status' => 'nullable|string',
            'dietary_restrictions'       => 'nullable|array',
            'emergency_contact_relationship' => 'nullable|string|max:100',
        ]);

        $user->update(array_filter([
            'name'     => $data['name'],
            // Email is read-only - never allow changes
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
        ]));

        $user->syncRoles([$data['role']]);

        // Preserve existing numbers, never allow edits via this endpoint
        $existingProfile = $user->profile;
        $profileData = collect($data)->only((new Profile)->getFillable())->all();
        unset($profileData['employee_number'], $profileData['student_number']);
        $user->profile()->updateOrCreate([], $profileData);

        return redirect()->route('hive.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('hive.users.index')
            ->with('success', 'User deleted.');
    }

    private function generateUniqueNumber(bool $isStudent, ?int $departmentId): string
    {
        if (!$departmentId) {
            $departmentId = 0;
        }

        return $isStudent
            ? \App\Services\IdGenerator::generateStudentId($departmentId)
            : \App\Services\IdGenerator::generateEmployeeId($departmentId);
    }
}