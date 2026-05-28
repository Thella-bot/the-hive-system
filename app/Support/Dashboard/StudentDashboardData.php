<?php

namespace App\Support\Dashboard;

use App\Models\User;
use Illuminate\Support\Collection;
use App\Support\Dashboard\GeneralDashboardData;

class StudentDashboardData extends GeneralDashboardData
{
    public function __construct(public User $user)
    {
        parent::__construct($user);
    }

    public function getUpcomingAssignments(): Collection
    {
        return $this->getAssignmentsForStudent($this->user->load('programme'));
    }

    public function getModules(): Collection
    {
        return $this->user->modules ?? collect();
    }
}