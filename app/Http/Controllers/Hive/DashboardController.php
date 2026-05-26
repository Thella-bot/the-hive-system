<?php 

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\Announcement;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roles');
        $data = [];

        if ($user->hasRole('admin')) {
            $data = array_merge($data, $this->getAdminDashboardData());
        }

        if ($user->hasRole('instructor')) {
            $data = array_merge($data, $this->getInstructorDashboardData($user));
        }

        if ($user->hasRole('student')) {
            $data = array_merge($data, $this->getStudentDashboardData($user));
        }

        return Inertia::render('Hive/Dashboard', array_merge($data, ['user' => $user]));
    }

    private function getAdminDashboardData(): array
    {
        $counts = User::query()
            ->select('roles.name', \DB::raw('count(*) as total'))
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->groupBy('roles.name')
            ->get()
            ->pluck('total', 'name');

        return [
            'totalStudents' => $counts->get('student', 0),
            'totalInstructors' => $counts->get('instructor', 0),
            'totalStaff' => $counts->get('hr_staff', 0) + $counts->get('admin', 0),
            'pendingApprovals' => User::whereNull('approved_at')->count(),
            'pendingLeaveRequests' => LeaveRequest::where('status', 'pending')->count(),
        ];
    }

    private function getInstructorDashboardData(User $user): array
    {
        return [
            'myModulesCount' => $user->instructedModules()->count(),
            'upcomingAssignments' => $this->getUpcomingAssignments($user),
            'recentSubmissions' => Submission::whereNull('grade')
                ->whereHas('assignment', fn ($q) => $q->where('instructor_id', $user->id))
                ->with('assignment', 'student')
                ->latest()
                ->take(5)
                ->get(),
        ];
    }

    private function getStudentDashboardData(User $user): array
    {
        return [
            'upcomingDeadlines' => $this->getUpcomingAssignments($user),
            'recentGrades' => Submission::where('student_id', $user->id)
                ->whereNotNull('grade')
                ->with('assignment.module')
                ->latest('graded_at')
                ->take(5)
                ->get(),
            'announcements' => Announcement::visibleTo($user)->latest()->take(3)->get(),
        ];
    }

    private function getUpcomingAssignments(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Assignment::query()
            ->forUser($user)
            ->where('due_date', '>', now())
            ->orderBy('due_date')
            ->take(5)
            ->get();
    }
}