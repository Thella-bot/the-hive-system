<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\AdminDashboardData;
use App\Services\Dashboard\InstructorDashboardData;
use App\Services\Dashboard\NonAcademicStaffDashboardData;
use App\Services\Dashboard\StudentDashboardData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = [];

        if ($user->hasRole(['super-admin', 'school-admin'])) {
            $data = app(AdminDashboardData::class)->getData($user);
        } elseif ($user->hasRole('academic_staff')) {
            $data = app(InstructorDashboardData::class)->getData($user);
        } elseif ($user->hasRole('non_academic_staff')) {
            $data = app(NonAcademicStaffDashboardData::class)->getData($user);
        } elseif ($user->hasRole('student')) {
            $data = app(StudentDashboardData::class)->getData($user);
        }

        return Inertia::render('Hive/Dashboard', $data);
    }
}
