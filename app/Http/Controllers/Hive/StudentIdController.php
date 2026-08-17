<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\GeneratesDocumentPdfs;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentIdController extends Controller
{
    use GeneratesDocumentPdfs;
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
        $card = $this->pdfCardData($target);

        return $this->generatePdf('pdf.student-id-card', [
            'name'          => $card['name'],
            'studentNumber' => $card['student_number'],
            'year'          => $card['year'],
            'programme'     => $card['programme'],
            'initials'      => $card['initials'],
            'photoPath'     => $card['photo_path'],
        ], 'Student_ID_' . ($target->profile?->student_number ?? $target->id) . '.pdf', $target->id, 'local', [0, 0, 242, 153]);
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
            // A browser-loadable URL for the on-screen preview. Null when the
            // student hasn't uploaded a custom photo - the UI falls back to
            // an initials avatar rather than the generic ui-avatars.com image.
            'photo_url' => $target->profile_photo_path ? $target->profile_photo_url : null,
            'initials' => $this->initials($target->name),
            'qr_data' => $profile?->student_number ?? config('institution.abbreviation') . '-' . $target->id,
        ];
    }

    /**
     * Same as cardData(), plus a filesystem path dompdf can actually read.
     * dompdf renders from disk, not over HTTP, and has remote image fetching
     * disabled by default, so the browser-facing photo_url above won't
     * render inside the PDF - this resolves the real local file instead.
     */
    private function pdfCardData(User $target): array
    {
        return array_merge($this->cardData($target), [
            'photo_path' => $this->resolvePhotoPath($target),
        ]);
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

        if (!$disk->exists($target->profile_photo_path)) {
            return null;
        }

        $path = $disk->path($target->profile_photo_path);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $path = str_replace('\\', '/', $path);
        }

        return $path;
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $initials = collect($parts)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');

        return $initials ?: '?';
    }
}
