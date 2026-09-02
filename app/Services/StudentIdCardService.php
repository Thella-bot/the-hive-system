<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\PDF;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

/**
 * Shared service that builds the data and PDF configuration for a student
 * ID card, used by both StudentIdController (web preview + download) and
 * DocumentFactory (document production / audit).
 *
 * Centralising this logic ensures the on-screen preview, the standalone
 * PDF download, and the document-production batch flow all render the
 * card with identical data and formatting.
 */
class StudentIdCardService
{
    /**
     * Data for the on-screen Vue preview (snake_case keys).
     */
    public function cardData(User $student): array
    {
        $student->loadMissing(['profile', 'programme', 'profile.cohort']);

        $profile = $student->profile;
        $cohort = $profile?->cohort;
        $qrData = $profile?->student_number ?? config('institution.abbreviation') . '-' . $student->id;

        return [
            'name'           => $student->name,
            'email'          => $student->email,
            'student_number' => $profile?->student_number,
            'programme'      => $student->programme?->name,
            'cohort'         => $cohort?->name,
            'year'           => $profile?->enrollment_date?->format('Y') ?? now()->format('Y'),
            'valid_until'    => ($cohort?->end_date ?? $profile?->expected_graduation_date)?->format('M Y'),
            'photo_url'      => $student->profile_photo_path ? $student->profile_photo_url : null,
            'initials'       => $this->initials($student->name),
            'qr_data'        => $qrData,
            'qr_code'        => $this->renderQrCode($qrData),
        ];
    }

    /**
     * Data mapped to the variable names the Blade template expects
     * (camelCase keys, filesystem photo path instead of URL).
     */
    public function templateData(User $student): array
    {
        $card = $this->cardData($student);

        return [
            'name'          => $card['name'],
            'email'         => $card['email'],
            'studentNumber' => $card['student_number'],
            'year'          => $card['year'],
            'programme'     => $card['programme'],
            'cohort'        => $card['cohort'],
            'validUntil'    => $card['valid_until'],
            'initials'      => $card['initials'],
            'photoPath'     => $this->resolvePhotoPath($student),
            'qrCode'        => $card['qr_code'],
        ];
    }

    /**
     * Apply the card-specific paper size and register the Oswald
     * condensed display font on the dompdf instance.
     */
    public function configurePdf(PDF $pdf): PDF
    {
        $pdf->setPaper([0, 0, 242, 316]);

        $pdf->getDomPDF()->getFontMetrics()->registerFont(
            ['family' => 'Oswald', 'style' => 'normal', 'weight' => '900'],
            public_path('fonts/Oswald-Bold.ttf')
        );

        return $pdf;
    }

    public function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = collect($parts)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');

        return $initials ?: '?';
    }

    /**
     * Resolve the student's uploaded profile photo to an absolute
     * filesystem path dompdf can read directly. Returns null when no
     * custom photo has been uploaded — the template falls back to
     * initials.
     */
    public function resolvePhotoPath(User $target): ?string
    {
        if (! $target->profile_photo_path) {
            return null;
        }

        $disk = Storage::disk($this->profilePhotoDisk());

        if (! $disk->exists($target->profile_photo_path)) {
            return null;
        }

        $path = $disk->path($target->profile_photo_path);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $path = str_replace('\\', '/', $path);
        }

        return $path;
    }

    /**
     * Render a verification QR code as a base64 PNG data URI so it
     * works identically in both the Vue preview and dompdf.
     */
    public function renderQrCode(string $data): string
    {
        $qrCode = new QrCode(
            data: $data,
            errorCorrectionLevel: ErrorCorrectionLevel::Quartile,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(17, 24, 39),
            backgroundColor: new Color(255, 255, 255),
        );

        return (new PngWriter())->write($qrCode)->getDataUri();
    }

    private function profilePhotoDisk(): string
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : config('jetstream.profile_photo_disk', 'public');
    }
}
