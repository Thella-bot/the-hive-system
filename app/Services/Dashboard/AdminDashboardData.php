<?php
declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Gradable;
use App\Models\LeaveRequest;
use App\Models\Programme;
use App\Models\Module;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        return Cache::remember('dashboard.admin.' . $user->id, 300, function () use ($user) {
            return $this->buildData($user);
        });
    }

 private function buildData(User $user): array
    {
        $facultyCount = User::role(['chef-instructor', 'pastry-instructor', 'sous-chef'])->count();
        $staffCount = User::role(['super-admin', 'it-support', 'admissions-officer', 'examination-cell', 'registrar', 'finance', 'procurement-manager', 'storekeeper', 'hr-manager', 'librarian', 'career-services', 'events-pr-manager', 'cafeteria-manager'])->count();

        return [
            // User Stats
            'totalStudents' => User::role('student')->count(),
            'totalInstructors' => $facultyCount,
            'totalStaff' => $staffCount,
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

            // Enrollment Stats
            'studentsEligibleForEnrollment' => $this->getStudentsEligibleForEnrollment(),
            'pendingRegistrations' => Application::where('registration_status', 'submitted')->count(),
        ];
    }

    /**
     * Get students who are eligible for enrollment (active but missing module enrollments).
     */
    private function getStudentsEligibleForEnrollment(): array
    {
        $currentAcademicYear = AcademicYear::current()->first();
        $academicYearName = $currentAcademicYear?->name ?? date('Y');
        $semester = now()->month <= 6 ? '1' : '2';

        $activeStudents = User::role('student')
            ->whereHas('profile', function ($q) {
                $q->where('status', 'active');
            })
            ->with(['profile', 'programme'])
            ->get();

        $eligible = [];

        foreach ($activeStudents as $student) {
            $programme = $student->programme;
            if (!$programme) {
                continue;
            }

            $yearLevel = $student->getCurrentSemesterContext()['year_level'] ?? 1;

            // Get required modules for current year/semester
            $requiredModules = $programme->modules()
                ->wherePivot('year_level', $yearLevel)
                ->wherePivot('semester', (int) $semester)
                ->pluck('modules.id')
                ->toArray();

            if (empty($requiredModules)) {
                continue;
            }

            // Get enrolled modules
            $enrolledModules = Enrollment::where('user_id', $student->id)
                ->where('academic_year', $academicYearName)
                ->where('semester', $semester)
                ->pluck('module_id')
                ->toArray();

            $missingModules = array_diff($requiredModules, $enrolledModules);

            if (!empty($missingModules)) {
                $eligible[] = [
                    'student' => $student,
                    'year_level' => $yearLevel,
                    'semester' => $semester,
                    'missing_count' => count($missingModules),
                    'total_required' => count($requiredModules),
                ];
            }
        }

        return [
            'count' => count($eligible),
            'students' => array_slice($eligible, 0, 10),
        ];
    }

    private function getNewStudentsByMonth(): array
    {
        $dbDriver = DB::connection()->getDriverName();
        
        $monthExpr = $dbDriver === 'sqlite'
            ? "strftime('%m', created_at)"
            : "MONTH(created_at)";

        $results = User::role('student')
            ->whereYear('created_at', now()->year)
            ->selectRaw("$monthExpr as month, COUNT(*) as count")
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $key = $i < 10 ? '0' . $i : (string) $i;
            $months[Carbon::create(null, $i)->format('F')] = isset($results[$key]) ? (int) $results[$key] : 0;
        }

        return $months;
    }
}