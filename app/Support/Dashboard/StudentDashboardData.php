<?php

namespace App\Support\Dashboard;

use App\Models\User;

class StudentDashboardData
{
    public function __construct(public User $user)
    {
    }
}



