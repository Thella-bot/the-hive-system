<?php

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Gradable;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        $moduleIds = DB::table('module_user')->where('user_id', $user->id)->pluck('module_id')->toArray();

        return [
            // Student Stats
            'totalModules' => count($moduleIds),
            'totalSubmissions' => Submission::where('student_id', $user->id)->count(),
            'pendingSubmissions' => Submission::where('student_id', $user->id)
                ->whereNull('submitted_at')
                ->whereHas('gradable', fn($q) => $q->where('due_date', '>', now()))
                ->count(),

            // Upcoming Assessments
            'upcomingAssessments' => count($moduleIds) > 0
                ? Gradable::whereIn('module_id', $moduleIds)
                    ->where('due_date', '>', now())
                    ->orderBy('due_date')
                    ->with('module')
                    ->take(5)
                    ->get()
                : collect(),

            // Due Soon (within 7 days)
            'dueSoon' => count($moduleIds) > 0
                ? Gradable::whereIn('module_id', $moduleIds)
                    ->whereBetween('due_date', [now(), now()->addDays(7)])
                    ->orderBy('due_date')
                    ->with('module')
                    ->take(5)
                    ->get()
                : collect(),

            // Recent Grades
            'recentGrades' => Submission::where('student_id', $user->id)
                ->whereNotNull('grade')
                ->with('gradable.module')
                ->latest('graded_at')
                ->take(5)
                ->get(),

            // Pending Submissions
            'pendingSubmissionsList' => Submission::where('student_id', $user->id)
                ->whereNull('submitted_at')
                ->with('gradable.module')
                ->whereHas('gradable', fn($q) => $q->where('due_date', '>', now()))
                ->take(5)
                ->get(),

            // Announcements
            'announcements' => Announcement::visibleTo($user)
                ->latest()
                ->take(5)
                ->get(),

            // Upcoming Events
            'upcomingEvents' => Event::where('start_date', '>', now())
                ->orderBy('start_date')
                ->take(5)
                ->get(),

            // Progress Stats
            'averageGrade' => $this->calculateAverageGrade($user),
            'completedModules' => $this->getCompletedModulesCount($user, $moduleIds),
        ];
    }

    private function calculateAverageGrade(User $user)
    {
        $avg = Submission::where('student_id', $user->id)
            ->whereNotNull('grade')
            ->avg('grade');

        return $avg ? round($avg, 1) : null;
    }

    private function getCompletedModulesCount(User $user, array $moduleIds)
    {
        if (empty($moduleIds)) {
            return 0;
        }

        return DB::table('modules')
            ->join('gradables', 'modules.id', '=', 'gradables.module_id')
            ->join('submissions', 'gradables.id', '=', 'submissions.gradable_id')
            ->where('submissions.student_id', $user->id)
            ->where('submissions.grade', '>=', 0)
            ->whereIn('modules.id', $moduleIds)
            ->distinct('modules.id')
            ->count('modules.id');
    }
}