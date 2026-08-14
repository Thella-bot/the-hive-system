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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
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
            'programmes'  => Programme::orderBy('name')->get(['id', 'name', 'department_id']),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($data['roles']);

        $profileData = collect($data)->only((new Profile)->getFillable())->all();

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

        return redirect()->route('hive.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): Response
    {
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
        $user->load(['roles', 'profile']);
        $isAdmin = auth()->user()?->isAdmin();

        return Inertia::render('Hive/Users/Edit', [
            'managedUser'        => $user,
            'roles'       => Role::orderBy('name')->get(['id', 'name']),
            'departments' => Department::active()->select('id', 'name')->get(),
            'cohorts'     => Cohort::active()->with('department:id,name')->select('id', 'name', 'department_id')->get(),
            'programmes'  => Programme::orderBy('name')->get(['id', 'name']),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $userData = [
            'name'          => $data['name'],
            'email'         => $data['email'],
            'programme_id'  => $data['programme_id'] ?? null,
            'approved_at'   => $data['approved_at'] ?? null,
        ];

        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $profileData = collect($data)->only((new Profile)->getFillable())->all();

        DB::transaction(function () use ($user, $userData, $profileData, $data) {
            $user->update($userData);
            $user->syncRoles($data['roles']);

            if ($user->profile()->exists()) {
                $user->profile()->update($profileData);
            } else {
                $user->profile()->create($profileData);
            }
        });

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
