<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentIdCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentIdController extends Controller
{
    public function __construct(protected StudentIdCardService $idCard) {}

    /**
     * Show the ID card for the current user, or (for staff who can manage
     * students) for a specific student.
     */
    public function show(Request $request, ?User $student = null)
    {
        if ($request->routeIs('hive.students.id-card') && $student === null) {
            abort(404);
        }

        $target = $this->resolveTarget($request, $student);
        $viewingOther = $target->id !== $request->user()->id;

        return Inertia::render('Hive/StudentIdCard', [
            'student_id' => $this->idCard->cardData($target),
            'can_manage' => $request->user()->can('viewAny', User::class),
            'download_url' => $viewingOther
                ? route('hive.students.id-card.download', ['student' => $target->id])
                : route('hive.student-id.download'),
        ]);
    }

    /**
     * Download a print-ready PDF of the ID card.
     */
    public function download(Request $request, ?User $student = null)
    {
        if ($request->routeIs('hive.students.id-card.download') && $student === null) {
            abort(404);
        }

        $target = $this->resolveTarget($request, $student);

        $pdf = Pdf::loadView('pdf.student-id-card', $this->idCard->templateData($target));
        $this->idCard->configurePdf($pdf);

        return $pdf->download('Student_ID_' . ($target->profile?->student_number ?? $target->id) . '.pdf');
    }

    /**
     * Work out which user's card is being requested and authorize it.
     */
    private function resolveTarget(Request $request, ?User $student): User
    {
        $user = $request->user();

        if ($student === null || $student->id === $user->id) {
            return $user;
        }

        // Viewing someone else's card requires the same ability used to
        // manage the student list (super-admin, registrar, etc.).
        $this->authorize('viewAny', User::class);

        return $student;
    }
}
