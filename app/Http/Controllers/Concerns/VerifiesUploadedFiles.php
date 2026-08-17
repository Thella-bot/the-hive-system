<?php
declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait VerifiesUploadedFiles
{
    /**
     * Verify that an uploaded file's actual content matches its declared MIME type.
     * This prevents MIME type spoofing attacks where malicious files are disguised as images/documents.
     */
    protected function verifyFileContent(UploadedFile $file, ?string $expectedMime = null): void
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualMime = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        $expected = $expectedMime ?: $file->getMimeType();

        if ($actualMime !== $expected) {
            \Log::warning('File MIME mismatch', [
                'path' => $file->getRealPath(),
                'declared' => $expected,
                'actual' => $actualMime,
                'name' => $file->getClientOriginalName(),
            ]);
            abort(400, 'Uploaded file content does not match its declared type.');
        }
    }

    /**
     * Get a safe download filename from user input.
     */
    protected function safeDownloadName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    }
}
