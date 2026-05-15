<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\LeaveRequest;
use App\Models\Profile;
use App\Models\Submission;
use App\Policies\AssignmentPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\SubmissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        LeaveRequest::class => LeaveRequestPolicy::class,
        Assignment::class => AssignmentPolicy::class,
        Submission::class => SubmissionPolicy::class,
        Profile::class => ProfilePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
