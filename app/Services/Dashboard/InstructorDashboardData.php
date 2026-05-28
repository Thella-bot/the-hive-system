<?php

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\Document;
use App\Models\Gradable;
use App\Models\Submission;
use App\Models\User;

class InstructorDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        $instructedModuleIds = $user->instructedModules()->pluck('module_id');

        return [
            // Instructor Stats
            'myModulesCount' => $user->instructedModules()->count(),
            'totalStudents' => \DB::table('module_user')->whereIn('module_id', $instructedModuleIds)->distinct('user_id')->count(),
            'totalAssessments' => Gradable::where('instructor_id', $user->id)->count(),
            'pendingGrades' => Submission::whereNull('grade')
                ->whereHas('gradable', fn($q) => $q->where('instructor_id', $user->id))
                ->whereNotNull('submitted_at')
                ->count(),

            // Upcoming Assessments (next 30 days)
            'upcomingAssessments' => Gradable::where('instructor_id', $user->id)
                ->where('due_date', '>', now())
                ->where('due_date', '<=', now()->addDays(30))
                ->orderBy('due_date')
                ->with('module')
                ->take(8)
                ->get(),

            // Recent Submissions (ungraded)
            'recentSubmissions' => Submission::whereNull('grade')
                ->whereHas('gradable', fn($q) => $q->where('instructor_id', $user->id))
                ->whereNotNull('submitted_at')
                ->with('gradable.module', 'student')
                ->latest('submitted_at')
                ->take(8)
                ->get(),

            // Recently Graded
            'recentlyGraded' => Submission::whereNotNull('grade')
                ->whereHas('gradable', fn($q) => $q->where('instructor_id', $user->id))
                ->with('gradable.module', 'student')
                ->latest('graded_at')
                ->take(5)
                ->get(),

            // Recent Module Documents
            'recentDocuments' => Document::whereIn('module_id', $instructedModuleIds)
                ->where('is_published', true)
                ->latest()
                ->take(5)
                ->get(),

            // Class Performance Snapshot
            'classAverage' => $this->calculateClassAverage($user),
            'classAverages' => $this->getClassAverages($user),
        ];
    }

    private function calculateClassAverage(User $user)
    {
        return Submission::whereHas('gradable', fn($q) => $q->where('instructor_id', $user->id))
            ->whereNotNull('grade')
            ->avg('grade');
    }

    private function getClassAverages(User $user)
    {
        return Submission::query()
            ->join('gradables', 'submissions.gradable_id', '=', 'gradables.id')
            ->join('modules', 'gradables.module_id', '=', 'modules.id')
            ->where('gradables.instructor_id', $user->id)
            ->whereNotNull('submissions.grade')
            ->select('modules.name', \DB::raw('AVG(submissions.grade) as average_grade'))
            ->groupBy('modules.name')
            ->pluck('average_grade', 'modules.name');
    }
}