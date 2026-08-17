<?php

namespace App\Jobs;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GenerateDocumentPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $view,
        public array $data,
        public string $fileName,
        public int $userId,
        public string $disk = 'local',
        public int $ttl = 3600,
    ) {}

    public function handle(): void
    {
        $user = User::findOrFail($this->userId);

        $pdf = Pdf::loadView($this->view, $this->data);
        $path = "documents/{$this->fileName}";

        Storage::disk($this->disk)->put($path, $pdf->output());

        Cache::put("document.pdf.{$this->fileName}", $path, $this->ttl);

        $user->update([
            'last_generated_document' => $path,
            'last_generated_at' => now(),
        ]);
    }
}

