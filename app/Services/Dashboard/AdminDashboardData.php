<?php

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Document;
use App\Models\Gradable;
use App\Models\LeaveRequest;
use App\Models\Programme;
use App\Models\Module;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        $totalStaff = User::role(['non_academic_staff', 'school-admin', 'super-admin'])->count();

        return [
            // User Stats
            'totalStudents' => User::role('student')->count(),
            'totalInstructors' => User::role('academic_staff')->count(),
            'totalStaff' => $totalStaff,
            'totalUsers' => User::count(),
            'pendingApplications' => Application::where('status', 'pending')->count(),

            // Academic Stats
            'totalProgrammes' => Programme::count(),
            'totalCourses' => Module::count(),
            'totalCohorts' => Cohort::count(),
            'activeAcademicYear' => AcademicYear::where('is_current', true)->first()?->name,

            // Content Stats
            'totalAnnouncements' => Announcement::count(),
            'totalDocuments' => Document::count(),
            'totalGradables' => Gradable::count(),

            // Pending Items
            'pendingApprovals' => User::whereNull('approved_at')->count(),
            'pendingLeaveRequests' => LeaveRequest::where('status', 'pending')->count(),

            // Activity Feed
            'recentUsers' => User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']),
            'recentSubmissions' => Submission::with(['student', 'gradable'])
                ->whereNotNull('submitted_at')
                ->latest('submitted_at')
                ->take(5)
                ->get(),

            // Quick Stats
            'newStudentsThisMonth' => User::role('student')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'pendingGrades' => Submission::whereNull('grade')
                ->whereNotNull('submitted_at')
                ->count(),
            'newStudentsByMonth' => $this->getNewStudentsByMonth(),
        ];
    }

    private function getNewStudentsByMonth()
    {
        $students = User::role('student')
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[Carbon::create(null, $i)->format('F')] = $students[$i] ?? 0;
        }

        return $months;
    }
}