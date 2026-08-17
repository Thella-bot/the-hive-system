<?php

namespace App\Http\Middleware;

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

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'profile_photo_url' => $user->profile_photo_url,
                    'roles'             => $user->getRoleNames(),
                    'permissions'       => collect($user->getAllPermissions())->pluck('name')->toArray(),
                    'needs_registration' => $user->hasRole('student') && $user->needsRegistration(),
                    'profile'           => $user->profile?->load('department:id,name', 'cohort:id,name') ?? null,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
            'unreadNotificationsCount' => $user ? $this->cachedNotificationsCount($user) : 0,
        ]);
    }

    private function cachedNotificationsCount($user): int
    {
        try {
            return cache()->remember(
                "notifications.unread.{$user->id}",
                60,
                fn() => $user->unreadNotifications()->count()
            );
        } catch (\Throwable) {
            return $user->unreadNotifications()->count();
        }
    }
}
