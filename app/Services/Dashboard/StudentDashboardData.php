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
            // Student's Program
            'programme' => $user->programme ? $user->programme()->with('modules')->first() : null,

            // Student Stats
            'totalModules' => count($moduleIds),
            'totalSubmissions' => Submission::where('student_id', $user->id)->count(),
            'pendingSubmissions' => Submission::where('student_id', $user->id)
                ->whereNull('submitted_at')
                ->whereHas('gradable', fn($q) => $q->where('due_date', '>', now()))
                ->count(),

            // Upcoming Assessments
            'upcomingAssessments' => $this->getUpcomingAssessments($moduleIds),

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
            'upcomingEvents' => Event::where('start', '>', now())
                ->orderBy('start')
                ->take(5)
                ->get(),

            // Progress Stats
            'averageGrade' => $this->calculateAverageGrade($user),
            'completedModules' => $this->getCompletedModulesCount($user, $moduleIds),
        ];
    }

private function getUpcomingAssessments(array $moduleIds)
    {
        if (empty($moduleIds)) {
            return collect();
        }

        $assessments = Gradable::whereIn('module_id', $moduleIds)
            ->where('due_date', '>', now())
            ->orderBy('due_date')
            ->with('module')
            ->take(5)
            ->get();

        $assessments->each(function ($assessment) {
            $assessment->is_due_soon = $assessment->due_date->isBefore(now()->addDays(7));
        });

        return $assessments;
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

        return Gradable::whereIn('module_id', $moduleIds)
            ->whereHas('submissions', fn($q) => $q->where('student_id', $user->id)->whereNotNull('grade'))
            ->distinct('module_id')
            ->count('module_id');
    }
}