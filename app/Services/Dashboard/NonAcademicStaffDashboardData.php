<?php

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\Announcement;
use App\Models\Application;
use App\Models\Document;
use App\Models\Event;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\User;

class NonAcademicStaffDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        $roleNames = $user->roles->pluck('name')->toArray();

        return [
            'staffLeaveBalance' => $user->profile?->leave_balance ?? 0,
            'staffPendingLeaveRequests' => LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'staffApprovedLeaveRequests' => LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->count(),
            'staffLatestPayslip' => Payslip::query()
                ->where('user_id', $user->id)
                ->latest('pay_period_end')
                ->first(),
            'pendingApplications' => Application::query()
                ->where('status', 'pending')
                ->count(),
            'recentApplications' => Application::query()
                ->with(['programme:id,name', 'variant:id,label'])
                ->latest()
                ->take(5)
                ->get(),
            'recentDocuments' => Document::query()
                ->with('latestVersion', 'module:id,name,code')
                ->where('is_published', true)
                ->where(function ($query) use ($roleNames) {
                    $query->whereNull('visible_to_roles');

                    foreach ($roleNames as $roleName) {
                        $query->orWhereJsonContains('visible_to_roles', $roleName);
                    }
                })
                ->latest()
                ->take(5)
                ->get(),
            'announcements' => Announcement::query()
                ->visibleTo($user)
                ->latest()
                ->take(5)
                ->get(),
            'upcomingEvents' => Event::query()
                ->where('start', '>=', now())
                ->orderBy('start')
                ->take(5)
                ->get(),
        ];
    }
}
