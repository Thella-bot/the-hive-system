<?php

namespace App\Http\Middleware;

use App\Services\Dashboard\AdminDashboardData;
use App\Services\Dashboard\InstructorDashboardData;
use App\Services\Dashboard\NonAcademicStaffDashboardData;
use App\Services\Dashboard\StudentDashboardData;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $dashboardData = [];

        if ($user) {
            $user->load('roles.permissions');

            $dashboardData = match (true) {
                // Admin: super-admin, it-support
                $user->hasAnyRole(['super-admin', 'it-support']) => (new AdminDashboardData())->getData($user),
                // Faculty: chef-instructor, pastry-instructor, sous-chef
                $user->hasAnyRole(['chef-instructor', 'pastry-instructor', 'sous-chef']) => (new InstructorDashboardData())->getData($user),
                // Non-academic staff (HR, finance, procurement, events, etc.)
                $user->hasAnyRole([
                    'finance',
                    'hr-manager',
                    'procurement-manager',
                    'storekeeper',
                    'librarian',
                    'career-services',
                    'events-pr-manager',
                    'cafeteria-manager',
                    'admissions-officer',
                    'registrar',
                    'examination-cell',
                    'academic-director',
                    'program-coordinator',
                ]) => (new NonAcademicStaffDashboardData())->getData($user),
                // Student
                $user->hasRole('student') => (new StudentDashboardData())->getData($user),
                default => [],
            };
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'profile_photo_url' => $user->profile_photo_url,
                    'roles'             => $user->getRoleNames(),
                    'permissions'       => collect($user->getAllPermissions())->pluck('name')->toArray(),
                    'needs_registration'=> $user->hasRole('student') && $user->needsRegistration(),
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
            'unreadNotificationsCount' => $user ? $user->unreadNotifications()->count() : 0,
        ], $dashboardData);
    }
}
