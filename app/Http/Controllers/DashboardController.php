<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\Department;
use App\Models\Profile;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = [
            'departments'     => Department::active()->count(),
            'active_cohorts'  => Cohort::active()->count(),
            'active_students' => Profile::whereNotNull('student_number')
                ->where('status', 'active')
                ->count(),
            'staff'           => Profile::whereNotNull('employee_number')->count(),
        ];

        $currentYear = AcademicYear::current()->first();

        $recentCohorts = Cohort::with(['department', 'academicYear'])
            ->withCount('students')
            ->active()
            ->latest()
            ->take(6)
            ->get();

        $departmentSnapshot = Department::active()
            ->withCount([
                'cohorts as active_cohort_count' => fn ($q) => $q->where('is_active', true),
                'staff',
            ])
            ->get();

        return Inertia::render('Dashboard', [
            'stats'               => $stats,
            'currentYear'         => $currentYear,
            'recentCohorts'       => $recentCohorts,
            'departmentSnapshot'  => $departmentSnapshot,
        ]);
    }
}
