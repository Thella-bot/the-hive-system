<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\AcademicHistory;
use App\Models\AcademicYear;
use App\Models\User;
use App\Services\StudentPromotionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentAdvancementController extends Controller
{
    public function __construct(
        protected StudentPromotionService $promotionService,
    ) {}

    public function index(Request $request)
    {
        $results = $this->promotionService->getEligibleStudents();

        $academicHistories = AcademicHistory::with(['user', 'academicYear', 'programme'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Hive/Advancement/Index', [
            'eligibility' => $results,
            'history' => $academicHistories,
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
        ]);
    }

    public function show(User $student)
    {
        $currentYear = AcademicYear::current()->first();
        $assessment = $this->promotionService->assessStudent($student, $currentYear);

        $history = AcademicHistory::forUser($student->id)
            ->with(['academicYear', 'programme'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Hive/Advancement/Show', [
            'student' => $student->load('profile'),
            'assessment' => $assessment,
            'history' => $history,
        ]);
    }

    public function promote(Request $request, User $student)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $currentYear = AcademicYear::current()->first();

        if (!$currentYear) {
            return back()->with('error', 'No current academic year set.');
        }

        $result = $this->promotionService->promoteStudent(
            $student,
            $currentYear,
            $request->input('notes', '')
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function promoteAll(Request $request)
    {
        $results = $this->promotionService->promoteAllEligible();

        $message = "Promoted: {$results['total_promoted']}, Graduated: {$results['total_graduated']}";

        if ($results['total_failed'] > 0) {
            $message .= ", Failed: {$results['total_failed']}";
            return back()->with('warning', $message);
        }

        return back()->with('success', $message);
    }
}
