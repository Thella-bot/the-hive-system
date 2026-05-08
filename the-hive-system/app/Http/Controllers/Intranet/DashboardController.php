<?php namespace App\Http\Controllers\Intranet;

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
        $user = auth()->user();
        $data = [];

        if ($user->hasRole('admin')) {
            $data['totalStudents'] = User::role('student')->count();
            $data['totalInstructors'] = User::role('instructor')->count();
            $data['totalStaff'] = User::role(['hr_staff','admin'])->count();
            $data['pendingApprovals'] = User::role('unapproved')->count();
            $data['pendingLeaveRequests'] = LeaveRequest::where('status','pending')->count();
        }

        if ($user->hasRole('instructor')) {
            $data['myModulesCount'] = $user->instructedModules()->count();
            $data['upcomingAssignments'] = Assignment::where('instructor_id', $user->id)
                ->where('due_date', '>', now())
                ->orderBy('due_date')
                ->take(5)
                ->get();
            $data['recentSubmissions'] = Submission::whereIn('assignment_id', Assignment::where('instructor_id', $user->id)->pluck('id'))
                ->whereNull('grade')
                ->with('assignment','student')
                ->latest()
                ->take(5)
                ->get();
        }

        if ($user->hasRole('student')) {
            $moduleIds = $user->modules()->pluck('id');
            $data['upcomingDeadlines'] = Assignment::whereIn('module_id', $moduleIds)
                ->where('due_date', '>', now())
                ->orderBy('due_date')
                ->take(5)
                ->get();
            $data['recentGrades'] = Submission::where('student_id', $user->id)
                ->whereNotNull('grade')
                ->with('assignment.module')
                ->latest('graded_at')
                ->take(5)
                ->get();
            $data['announcements'] = Announcement::visibleTo($user)->latest()->take(3)->get();
        }

        return Inertia::render('Intranet/Dashboard', array_merge($data, [
            'user' => $user->load('roles'),
        ]));
    }
}