<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\AdminDashboardData;
use App\Services\Dashboard\InstructorDashboardData;
use App\Services\Dashboard\NonAcademicStaffDashboardData;
use App\Services\Dashboard\StudentDashboardData;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly AdminDashboardData $adminDashboardData,
        private readonly InstructorDashboardData $instructorDashboardData,
        private readonly NonAcademicStaffDashboardData $nonAcademicStaffDashboardData,
        private readonly StudentDashboardData $studentDashboardData,
    ) {}

    public function index()
    {
        $user = auth()->user();

        $data = match ($this->roleService->getDashboardDataType($user)) {
            'admin' => $this->adminDashboardData->getData($user),
            'instructor' => $this->instructorDashboardData->getData($user),
            'non_academic_staff' => $this->nonAcademicStaffDashboardData->getData($user),
            'student' => $this->studentDashboardData->getData($user),
            default => [],
        };

        return Inertia::render('Hive/Dashboard', $data);
    }
}
