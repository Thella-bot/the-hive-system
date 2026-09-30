<?php
declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Contracts\DashboardData;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\Gradable;
use App\Models\Placement;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AlumniDashboardData implements DashboardData
{
    public function getData(User $user): array
    {
        return Cache::remember('dashboard.alumni.' . $user->id, 300, function () use ($user) {
            return $this->buildData($user);
        });
    }

    private function buildData(User $user): array
    {
        $profile = $user->profile;

        return [
            'programme' => $user->programme?->name,
            'graduationDate' => $profile?->graduation_date,
            'studentNumber' => $profile?->student_number,

            // A module counts as completed once the student has a graded
            // submission against one of its gradables.
            'modulesCompleted' => Gradable::whereHas(
                'submissions',
                fn($q) => $q->where('student_id', $user->id)->whereNotNull('grade')
            )
                ->distinct()
                ->count('gradables.id'),

            'finalAverage' => $this->finalAverage($user),

            'placements' => Placement::where('student_id', $user->id)
                ->orderByDesc('placed_at')
                ->take(5)
                ->get(),

            'availableDocuments' => Document::where('is_published', true)
                ->where(function ($query) use ($user) {
                    $query->whereNull('visible_to_roles');
                    foreach ($user->roles as $role) {
                        $query->orWhereJsonContains('visible_to_roles', $role->name);
                    }
                })
                ->latest()
                ->take(5)
                ->get(),

            'announcements' => Announcement::visibleTo($user)->latest()->take(5)->get(),
        ];
    }

    private function finalAverage(User $user)
    {
        $avg = Submission::where('student_id', $user->id)
            ->whereNotNull('grade')
            ->avg('grade');

        return $avg ? round((float) $avg, 1) : null;
    }
}
