<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
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
        if ($request->routeIs('hive.students.id-card') && $student === null) {
            abort(404);
        }

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
        if ($request->routeIs('hive.students.id-card.download') && $student === null) {
            abort(404);
        }

        $target = $this->resolveTarget($request, $student);
        $card = $this->pdfCardData($target);

        $pdf = Pdf::loadView('pdf.student-id-card', [
            'name'          => $card['name'],
            'email'         => $card['email'],
            'studentNumber' => $card['student_number'],
            'year'          => $card['year'],
            'programme'     => $card['programme'],
            'cohort'        => $card['cohort'],
            'validUntil'    => $card['valid_until'],
            'initials'      => $card['initials'],
            'photoPath'     => $card['photo_path'],
            'qrCode'        => $card['qr_code'],
        ])->setPaper([0, 0, 242, 316]);

        // The COURSE value uses a condensed display font (matching the
        // card's reference design) so longer programme names still fit
        // on one line. Declaring it via @font-face in the blade file is
        // NOT sufficient - dompdf silently ignored it in testing and fell
        // back to a default serif font. It must be registered directly
        // with dompdf's FontMetrics before the PDF is rendered.
        $pdf->getDomPDF()->getFontMetrics()->registerFont(
            ['family' => 'Oswald', 'style' => 'normal', 'weight' => '900'],
            public_path('fonts/Oswald-Bold.ttf')
        );

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

    private function cardData(User $target): array
    {
        $profile = $target->profile;
        $cohort = $profile?->cohort;
        $qrData = $profile?->student_number ?? config('institution.abbreviation') . '-' . $target->id;

        return [
            'name' => $target->name,
            'email' => $target->email,
            'student_number' => $profile?->student_number,
            'programme' => $target->programme?->name,
            'cohort' => $cohort?->name,
            'year' => $profile?->enrollment_date?->format('Y') ?? now()->format('Y'),
            // Card validity mirrors the student's cohort end date (when known),
            // falling back to their expected graduation date. Both are the
            // most meaningful "this student is currently enrolled" signals
            // we have - there's no dedicated card-expiry field.
            'valid_until' => ($cohort?->end_date ?? $profile?->expected_graduation_date)?->format('M Y'),
            // A browser-loadable URL for the on-screen preview. Null when the
            // student hasn't uploaded a custom photo - the UI falls back to
            // an initials avatar rather than the generic ui-avatars.com image.
            'photo_url' => $target->profile_photo_path ? $target->profile_photo_url : null,
            'initials' => $this->initials($target->name),
            'qr_data' => $qrData,
            // Rendered once here (not client-side) so the on-screen preview
            // and the printed PDF are pixel-identical, and so dompdf - which
            // can't reach the browser's JS QR library - has an image it can
            // embed directly.
            'qr_code' => $this->renderQrCode($qrData),
        ];
    }

    /**
     * Render a verification QR code as a base64 PNG data URI.
     *
     * PNG (not SVG) because dompdf's inline-SVG support is unreliable for
     * anything beyond trivial shapes (see the note on the background image
     * in the PDF template) - a data URI keeps this working identically in
     * both the Vue preview and dompdf without any remote fetch.
     */
    private function renderQrCode(string $data): string
    {
        $qrCode = new QrCode(
            data: $data,
            errorCorrectionLevel: ErrorCorrectionLevel::Quartile,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(17, 24, 39), // matches card text (#111827)
            backgroundColor: new Color(255, 255, 255),
        );

        $result = (new PngWriter())->write($qrCode);

        return $result->getDataUri();
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
