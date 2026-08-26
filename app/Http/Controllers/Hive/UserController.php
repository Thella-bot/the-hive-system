<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hive\StoreUserRequest;
use App\Http\Requests\Hive\UpdateUserRequest;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\Profile;
use App\Models\Programme;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ReferenceDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected AuditService $audit,
    ) {}
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $paginatedUsers = User::with(['roles', 'profile.department', 'profile.cohort'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
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
            'roles'   => app(ReferenceDataService::class)->roles()->pluck('name'),
            'filters' => $request->only('search', 'role'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        $ref = app(ReferenceDataService::class);

        return Inertia::render('Hive/Users/Create', [
            'roles'       => $ref->roles(),
            'departments' => $ref->departments(),
            'cohorts'     => Cohort::with('department:id,name')->select('id', 'name', 'department_id')->get(),
            'programmes'  => $ref->programmes(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        // Sanitize inputs
        $data['name'] = strip_tags($data['name']);
        $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

        $user = User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($data['password']),
            'programme_id'       => $data['programme_id'] ?? null,
            'student_number'     => $data['student_number'] ?? null,
            'approved_at'        => $data['approved_at'] ?? null,
            'gender'             => $data['gender'] ?? null,
            'national_id_number' => $data['national_id_number'] ?? null,
        ]);

        $user->syncRoles($data['roles']);

        // Explicitly define allowed profile fields to prevent mass assignment
        $allowedProfileFields = [
            'first_name', 'last_name', 'date_of_birth', 'phone', 'address',
            'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
            'profile_picture_path', 'twitter_handle', 'linkedin_profile',
            'cohort_id', 'enrollment_date', 'expected_graduation_date', 'status',
            'dietary_restrictions', 'specialization', 'bio',
        ];

        // Only include fields that are in the allowed list
        $profileData = collect($data)->only($allowedProfileFields)->all();

        // Generate student/employee number if not provided
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
            $profileData['student_number'] = $profileData['student_number'] ?? $number;
        } else {
            $profileData['employee_number'] = $profileData['employee_number'] ?? $number;
        }

        $user->profile()->create($profileData);

        // Log audit trail
        $this->audit->logCreated($user);

        return redirect()->route('hive.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->load([
            'roles',
            'profile.department',
            'profile.cohort.department',
            'programme',
        ]);

        // Load applications (for applicants/students) - check if relationship exists
        $applications = method_exists($user, 'applications')
            ? $user->applications()->with(['programme', 'variant'])->latest()->limit(10)->get()
            : collect();

        // Load enrollments (for students) - check if relationship exists
        $enrollments = method_exists($user, 'enrollments')
            ? $user->enrollments()->with(['module'])->latest()->limit(10)->get()
            : collect();

        // Load certifications if relationship exists
        $certifications = method_exists($user, 'certifications')
            ? $user->certifications()->with(['module', 'awardedBy'])->latest()->limit(10)->get()
            : collect();

        // Count documents if relationship exists
        $documentCount = method_exists($user, 'documents')
            ? $user->documents()->count()
            : 0;

        return Inertia::render('Hive/Users/Show', [
            'managedUser' => $user,
            'applications' => $applications,
            'enrollments' => $enrollments,
            'certifications' => $certifications,
            'documentCount' => $documentCount,
            'programme' => $user->programme,
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load(['roles', 'profile']);
        $isAdmin = auth()->user()?->isAdmin();
        $ref = app(ReferenceDataService::class);

        return Inertia::render('Hive/Users/Edit', [
            'managedUser'        => $user,
            'roles'       => $ref->roles(),
            'departments' => $ref->departments(),
            'cohorts'     => Cohort::with('department:id,name')->select('id', 'name', 'department_id')->get(),
            'programmes'  => $ref->programmes(),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        // Sanitize inputs
        $data['name'] = strip_tags($data['name']);
        $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

        if (!auth()->user()->isAdmin()) {
            $administrativeRoles = ['super-admin', 'it-support'];
            foreach ($data['roles'] as $role) {
                if (in_array($role, $administrativeRoles, true)) {
                    abort(403);
                }
            }
        }

        $userData = [
            'name'               => $data['name'],
            'email'              => $data['email'],
            'programme_id'       => $data['programme_id'] ?? null,
            'student_number'     => $data['student_number'] ?? null,
            'approved_at'        => $data['approved_at'] ?? null,
            'gender'             => $data['gender'] ?? null,
            'national_id_number' => $data['national_id_number'] ?? null,
        ];

        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        // Explicitly define allowed profile fields to prevent mass assignment
        $allowedProfileFields = [
            'first_name', 'last_name', 'date_of_birth', 'phone', 'address',
            'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
            'profile_picture_path', 'twitter_handle', 'linkedin_profile',
            'cohort_id', 'enrollment_date', 'expected_graduation_date', 'status',
            'dietary_restrictions', 'specialization', 'bio',
        ];

        // Only include fields that are in the allowed list
        $profileData = collect($data)->only($allowedProfileFields)->all();

        // Capture old values for audit
        $oldValues = $user->getAttributes();
        if ($user->profile) {
            $oldValues['profile'] = $user->profile->getAttributes();
        }

        try {
            DB::transaction(function () use ($user, $userData, $profileData, $data) {
                $user->update($userData);
                $user->syncRoles($data['roles']);

                if ($user->profile()->exists()) {
                    $user->profile()->update($profileData);
                } else {
                    $user->profile()->create($profileData);
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update user. Please try again.')
                ->withInput();
        }

        // Log audit trail
        $user->refresh();
        $this->audit->logUpdated($user, $oldValues);

        return redirect()->route('hive.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Log audit trail before deletion
        $this->audit->logDeleted($user);

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
