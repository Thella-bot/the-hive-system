<?php

namespace App\Contracts;

use App\Models\User;

interface DashboardData
{
    /**
     * Get the dashboard data for a given user.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    public function getData(User $user): array;
}
