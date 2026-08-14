<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\ChatChannel;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\DisciplinaryAction;
use App\Models\Event;
use App\Models\Gradable;
use App\Models\LeaveRequest;
use App\Models\Module;
use App\Models\Payment;
use App\Models\Payslip;
use App\Models\Placement;
use App\Models\Programme;
use App\Models\Profile;
use App\Models\ShortCourse;
use App\Models\ShortCourseApplication;
use App\Models\Submission;
use App\Policies\AcademicYearPolicy;
use App\Policies\AchievementPolicy;
use App\Policies\ApplicationPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\ChatChannelPolicy;
use App\Policies\CohortPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DisciplinaryActionPolicy;
use App\Policies\EventPolicy;
use App\Policies\GradablePolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\ModulePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PayslipPolicy;
use App\Policies\PlacementPolicy;
use App\Policies\ProgrammePolicy;
use App\Policies\ProfilePolicy;
use App\Policies\ShortCourseApplicationPolicy;
use App\Policies\ShortCoursePolicy;
use App\Policies\SubmissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        LeaveRequest::class => LeaveRequestPolicy::class,
        Submission::class => SubmissionPolicy::class,
        Profile::class => ProfilePolicy::class,
        Department::class => DepartmentPolicy::class,
        Cohort::class => CohortPolicy::class,
        Gradable::class => GradablePolicy::class,
        Event::class => EventPolicy::class,
        AcademicYear::class => AcademicYearPolicy::class,
        Programme::class => ProgrammePolicy::class,
        Module::class => ModulePolicy::class,
        Payslip::class => PayslipPolicy::class,
        Application::class => ApplicationPolicy::class,
        Achievement::class => AchievementPolicy::class,
        Attendance::class => AttendancePolicy::class,
        ShortCourse::class => ShortCoursePolicy::class,
        ShortCourseApplication::class => ShortCourseApplicationPolicy::class,
        ChatChannel::class => ChatChannelPolicy::class,
        Payment::class => PaymentPolicy::class,
        Placement::class => PlacementPolicy::class,
        DisciplinaryAction::class => DisciplinaryActionPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}