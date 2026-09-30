<?php
declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Application;
use App\Models\Enrollment;
use App\Models\Gradable;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AcademicStaffDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        return Cache::remember('dashboard.academic_staff.' . $user->id, 300, function () use ($user) {
            return $this->buildData($user);
        });
    }

    private function buildData(User $user): array
    {
        $academicYear = AcademicYear::current()->first();
        $yearName = $academicYear?->name ?? now()->format('Y');
        $semester = now()->month <= 6 ? '1' : '2';

        return [
            'activeAcademicYear' => $academicYear?->name,
            'currentSemester' => $semester,

            // Register management
            'totalStudents' => User::role('student')->count(),
            'newStudentsThisMonth' => User::role('student')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),

            // Admissions queue this role is expected to clear
            'pendingApplications' => Application::where('status', 'pending')->count(),
            'pendingRegistrations' => Application::where('registration_status', 'submitted')->count(),

            // Enrollment and results
            'enrolledThisYear' => Enrollment::where('academic_year', $yearName)->count(),
            'ungradedSubmissions' => Submission::whereNull('grade')
                ->whereNotNull('submitted_at')
                ->count(),
            'pendingGrades' => $this->pendingGrades($yearName, $semester),
            'gradablesCount' => Gradable::count(),

            // Releases and results waiting on the examination cell
            'studentsAwaitingResults' => $this->studentsAwaitingResults($yearName),

            'recentApplications' => Application::with(['programme:id,name', 'variant:id,label'])
                ->latest()
                ->take(5)
                ->get(),

            'announcements' => Announcement::visibleTo($user)->latest()->take(5)->get(),
        ];
    }

    private function pendingGrades(string $yearName, string $semester): int
    {
        $moduleIds = Enrollment::where('academic_year', $yearName)
            ->where('semester', $semester)
            ->distinct()
            ->pluck('module_id');

        if ($moduleIds->isEmpty()) {
            return 0;
        }

        return Submission::whereNull('grade')
            ->whereNotNull('submitted_at')
            ->whereHas('gradable', fn($q) => $q->whereIn('module_id', $moduleIds))
            ->count();
    }

    /**
     * Students enrolled in the current year/semester with no graded work at
     * all, i.e. blocked from a results release.
     */
    private function studentsAwaitingResults(string $yearName): array
    {
        $moduleIds = Enrollment::where('academic_year', $yearName)
            ->distinct()
            ->pluck('module_id');

        if ($moduleIds->isEmpty()) {
            return ['count' => 0, 'students' => []];
        }

        $studentIds = Enrollment::where('academic_year', $yearName)
            ->distinct()
            ->pluck('user_id');

        $ungraded = Submission::whereNull('grade')
            ->whereIn('student_id', $studentIds)
            ->whereHas('gradable', fn($q) => $q->whereIn('module_id', $moduleIds))
            ->distinct()
            ->pluck('student_id');

        $students = User::whereIn('id', $ungraded)
            ->with('profile')
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name', 'email']);

        return [
            'count' => $ungraded->count(),
            'students' => $students,
        ];
    }
}
