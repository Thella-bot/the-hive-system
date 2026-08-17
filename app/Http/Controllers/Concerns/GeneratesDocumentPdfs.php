<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

trait GeneratesDocumentPdfs
{
    protected function generatePdf(string $view, array $data, string $fileName, int $userId, string $disk = 'local', array|string|null $paper = null): Response
    {
        $cacheKey = "document.pdf.{$fileName}";
        
        if (Cache::has($cacheKey)) {
            $path = Cache::get($cacheKey);
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->download($path);
            }
            Cache::forget($cacheKey);
        }

        $pdf = Pdf::loadView($view, $data);
        
        if ($paper) {
            $pdf->setPaper($paper);
        }
        
        return $pdf->stream($fileName);
    }
}
