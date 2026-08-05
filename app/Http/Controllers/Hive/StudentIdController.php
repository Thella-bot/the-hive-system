<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentIdController extends Controller
{
    /**
     * Show the ID card for the current user, or (for staff who can manage
     * students) for a specific student.
     */
    public function show(Request $request, ?User $student = null)
    {
        $target = $this->resolveTarget($request, $student);
        $viewingOther = $target->id !== $request->user()->id;

        return Inertia::render('Hive/StudentIdCard', [
            'student_id' => $this->cardData($target),
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
        $target = $this->resolveTarget($request, $student);

        $pdf = Pdf::loadView('pdf.student-id-card', [
            'card' => $this->cardData($target),
        ])->setPaper([0, 0, 242, 153]); // ~CR80 card size in points (3.375in x 2.125in landscape)

        return $pdf->download('Student_ID_'.($target->profile?->student_number ?? $target->id).'.pdf');
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

    private function cardData(User $target): array
    {
        $profile = $target->profile;

        return [
            'name' => $target->name,
            'email' => $target->email,
            'student_number' => $profile?->student_number,
            'programme' => $target->programme?->name,
            'cohort' => $profile?->cohort?->name,
            'year' => $profile?->enrollment_date?->format('Y') ?? now()->format('Y'),
            // dompdf renders from the filesystem, not over HTTP - resolving to a
            // real local path here (instead of the public URL used on screen)
            // is what makes the photo actually show up in the PDF.
            'photo_path' => $this->resolvePhotoPath($target),
            'initials' => $this->initials($target->name),
            'qr_data' => $profile?->student_number ?? 'HBCI-'.$target->id,
        ];
    }

    /**
     * Resolve the student's uploaded profile photo to an absolute filesystem
     * path dompdf can read directly. Returns null when no custom photo has
     * been uploaded (the default ui-avatars.com URL is a remote image and
     * isn't suitable for the PDF - the template falls back to initials).
     */
    private function resolvePhotoPath(User $target): ?string
    {
        if (!$target->profile_photo_path) {
            return null;
        }

        $disk = Storage::disk($target->profilePhotoDisk());

        return $disk->exists($target->profile_photo_path)
            ? $disk->path($target->profile_photo_path)
            : null;
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $initials = collect($parts)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');

        return $initials ?: '?';
    }
}
