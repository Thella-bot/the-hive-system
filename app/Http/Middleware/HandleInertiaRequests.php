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
                $user->hasRole(['super-admin', 'school-admin']) => (new AdminDashboardData())->getData($user),
                $user->hasRole('academic_staff') => (new InstructorDashboardData())->getData($user),
                $user->hasRole('non_academic_staff') => (new NonAcademicStaffDashboardData())->getData($user),
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
                    'roles'             => $user->getRoleNames()->toArray(),
                    'permissions'       => $user->getAllPermissions()->pluck('name')->toArray(),
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
