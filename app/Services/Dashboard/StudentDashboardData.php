<?php

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\Announcement;
use App\Models\Bookmark;
use App\Models\Event;
use App\Models\Gradable;
use App\Models\Invoice;
use App\Models\Programme;
use App\Models\StudentTask;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        $moduleIds = DB::table('module_user')->where('user_id', $user->id)->pluck('module_id')->toArray();

        $moduleProgress = $this->buildModuleProgress($user, $moduleIds);

        return [
            // Programme with modules (Bug D fix: was using belongsTo with first())
            'programme' => $user->programme_id
                ? Programme::with('modules:id,name,code')->find($user->programme_id)
                : null,

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

            // Bug B fix: these were undefined — now populated
            'gradeHistory' => Submission::where('student_id', $user->id)
                ->whereNotNull('grade')
                ->with('gradable.module')
                ->latest('graded_at')
                ->take(20)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'grade' => $s->grade,
                    'graded_at' => $s->graded_at,
                    'title' => $s->gradable?->title,
                    'type' => $s->gradable?->type?->value,
                ]),
            'moduleProgress' => $moduleProgress,
            'tasks' => StudentTask::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->take(20)
                ->get(['id', 'title', 'due_date', 'completed', 'created_at']),
            'bookmarks' => Bookmark::where('user_id', $user->id)
                ->with('bookmarkable')
                ->latest()
                ->take(10)
                ->get(),
            'recentActivities' => $this->buildRecentActivities($user),

            // Fee Information
            'invoices' => $this->getInvoices($user),
            'totalFees' => $this->getTotalFees($user),
            'totalPaid' => $this->getTotalPaid($user),
            'remainingBalance' => $this->getRemainingBalance($user),
        ];
    }

    private function getUpcomingAssessments(array $moduleIds)
    {
        if (empty($moduleIds)) {
            return collect();
        }

        $assessments = Gradable::whereIn('module_id', $moduleIds)
            ->where('due_date', '>', now())
            ->whereNotNull('due_date') // Bug E: guard against null due_date
            ->orderBy('due_date')
            ->with('module')
            ->take(5)
            ->get();

        $assessments->each(function ($assessment) {
            $assessment->is_due_soon = $assessment->due_date && $assessment->due_date->isBefore(now()->addDays(7));
        });

        return $assessments;
    }

    private function buildModuleProgress(User $user, array $moduleIds): array
    {
        if (empty($moduleIds)) {
            return [];
        }

        return DB::table('module_user')
            ->where('user_id', $user->id)
            ->join('modules', 'module_user.module_id', '=', 'modules.id')
            ->whereIn('module_user.module_id', $moduleIds)
            ->select('modules.id', 'modules.name', 'modules.code')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->code ? "{$m->code} — {$m->name}" : $m->name,
                'percentage' => 0, // placeholder; real percentage needs module-level grade calc
            ])
            ->toArray();
    }

    private function buildRecentActivities(User $user): array
    {
        $activities = [];

        // Grade activities
        $grades = Submission::where('student_id', $user->id)
            ->whereNotNull('graded_at')
            ->with('gradable.module')
            ->latest('graded_at')
            ->take(5)
            ->get();

        foreach ($grades as $s) {
            $activities[] = [
                'id' => "grade-{$s->id}",
                'type' => 'grade',
                'title' => 'Grade received',
                'description' => $s->gradable?->title . ' — ' . $s->grade . '%',
                'created_at' => $s->graded_at,
                'badge' => 'graded',
            ];
        }

        // Submission activities
        $subs = Submission::where('student_id', $user->id)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->take(3)
            ->get();

        foreach ($subs as $s) {
            $activities[] = [
                'id' => "sub-{$s->id}",
                'type' => 'submission',
                'title' => 'Work submitted',
                'description' => $s->gradable?->title,
                'created_at' => $s->submitted_at,
                'badge' => 'submitted',
            ];
        }

        // Sort all activities by created_at desc
        usort($activities, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return array_slice($activities, 0, 10);
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

    private function getInvoices(User $user)
    {
        return Invoice::where('user_id', $user->id)
            ->with('payments')
            ->orderBy('due_date', 'desc')
            ->take(10)
            ->get()
            ->map(fn($invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'type' => $invoice->type,
                'description' => $invoice->description,
                'amount' => $invoice->amount,
                'total_paid' => $invoice->total_paid,
                'balance' => $invoice->balance,
                'status' => $invoice->status,
                'status_label' => $invoice->status_label,
                'due_date' => $invoice->due_date,
                'is_paid' => $invoice->is_paid,
                'is_overdue' => $invoice->is_overdue,
            ]);
    }

    private function getTotalFees(User $user): float
    {
        return Invoice::where('user_id', $user->id)->sum('amount');
    }

    private function getTotalPaid(User $user): float
    {
        return Invoice::where('user_id', $user->id)
            ->get()
            ->sum('total_paid');
    }

    private function getRemainingBalance(User $user): float
    {
        return Invoice::where('user_id', $user->id)
            ->get()
            ->sum('balance');
    }
}