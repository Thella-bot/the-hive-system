<?php
declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Gradable;
use App\Models\Invoice;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ParentGuardianDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        return Cache::remember('dashboard.guardian.' . $user->id, 300, function () use ($user) {
            return $this->buildData($user);
        });
    }

    private function buildData(User $user): array
    {
        $wards = $user->wards()
            ->with(['programme:id,name', 'profile'])
            ->get();

        $wardIds = $wards->pluck('id')->all();

        // Guardians see a student's results but never their fee balance unless
        // the link was explicitly granted.
        $feeVisibleWardIds = $wards
            ->filter(fn($ward) => (bool) $ward->pivot->can_view_fees)
            ->pluck('id')
            ->all();

        return [
            'wards' => $wards->map(fn($ward) => [
                'id' => $ward->id,
                'name' => $ward->name,
                'student_number' => $ward->profile?->student_number,
                'programme' => $ward->programme?->name,
                'relationship' => $ward->pivot->relationship,
                'average_grade' => $this->averageGradeFor($ward->id),
                'pending_submissions' => Submission::where('student_id', $ward->id)
                    ->whereNull('submitted_at')
                    ->whereHas('gradable', fn($q) => $q->where('due_date', '>', now()))
                    ->count(),
            ])->values()->all(),

            'wardCount' => $wards->count(),

            'recentGrades' => $wardIds ? Submission::whereIn('student_id', $wardIds)
                ->whereNotNull('grade')
                ->with(['gradable.module', 'student'])
                ->latest('graded_at')
                ->take(8)
                ->get() : [],

            'upcomingAssessments' => $wardIds ? Gradable::whereHas(
                'module.students',
                fn($q) => $q->whereIn('users.id', $wardIds)
            )
                ->where('due_date', '>', now())
                ->whereNotNull('due_date')
                ->with('module')
                ->orderBy('due_date')
                ->take(8)
                ->get() : [],

            'announcements' => Announcement::visibleTo($user)->latest()->take(5)->get(),

            'upcomingEvents' => Event::where('start', '>', now())->orderBy('start')->take(5)->get(),

            // Fee visibility is opt-in per guardian link.
            'feeSummary' => $feeVisibleWardIds ? [
                'totalFees' => (float) Invoice::whereIn('user_id', $feeVisibleWardIds)->sum('amount'),
                'outstanding' => (float) Invoice::whereIn('user_id', $feeVisibleWardIds)
                    ->where('status', '!=', 'paid')
                    ->get()
                    ->sum(fn($invoice) => (float) $invoice->balance),
            ] : null,
        ];
    }

    private function averageGradeFor(int $studentId)
    {
        $avg = Submission::where('student_id', $studentId)->whereNotNull('grade')->avg('grade');

        return $avg ? round((float) $avg, 1) : null;
    }
}
