<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    /**
     * Allowed MIME types for attachments.
     */
    protected array $allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /**
     * Maximum file size in kilobytes (2MB).
     */
    protected int $maxFileSizeKb = 2048;

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'programme_id' => 'required|exists:programmes,id',
            'variant_id' => 'nullable|exists:programme_variants,id',
        ]);

        // Validate variant belongs to the selected programme
        if (!empty($validatedData['variant_id'])) {
            $variantExists = \App\Models\ProgrammeVariant::where('id', $validatedData['variant_id'])
                ->where('programme_id', $validatedData['programme_id'])
                ->exists();

            if (!$variantExists) {
                return back()->withErrors(['variant_id' => 'The selected payment option is not valid for this programme.'])->withInput();
            }
        }

        // Check for existing pending application with same email+programme
        $existingApplication = Application::where('email', $validatedData['email'])
            ->where('programme_id', $validatedData['programme_id'])
            ->where('status', 'pending')
            ->exists();

        if ($existingApplication) {
            return back()->withErrors(['email' => 'You already have a pending application for this programme.'])->withInput();
        }

        // Handle attachments separately - only validate if files are actually uploaded
        if ($request->hasFile('attachments')) {
            $request->validate([
                'attachments.*' => 'file|mimes:pdf,jpeg,png,gif,doc,docx|max:2048',
            ]);
        }

        try {
            // Link to authenticated user if available
            if (auth()->check()) {
                $validatedData['user_id'] = auth()->id();
            }

            // Handle file attachments
            $attachments = $this->processAttachments($request);
            if ($attachments !== null) {
                $validatedData['attachments'] = $attachments;
            }

            $application = Application::create($validatedData);
            Log::info('Application created successfully', ['application_id' => $application->id]);
            return back()->with('success', 'Your application has been submitted. We\'ll be in touch shortly!');
        } catch (\Exception $e) {
            Log::error('Application submission failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'There was a problem submitting your application. Please try again later.');
        }
    }

    /**
     * Process and store uploaded attachments.
     * Handles both flat files (attachments[0], attachments[1]) and keyed files (attachments[certificate], attachments[employer_letter]).
     */
    protected function processAttachments(Request $request): ?array
    {
        if (!$request->hasFile('attachments')) {
            return null;
        }

        $attachmentData = [];
        $files = $request->file('attachments');

        // If files is a simple array of UploadedFile objects (flat structure)
        if (is_array($files) && isset($files[0]) && $files[0] instanceof \Illuminate\Http\UploadedFile) {
            foreach ($files as $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    // Try to determine type from the original input name
                    // Default to 'additional' for flat structure
                    $attachmentData[] = $this->storeSingleFile($file, 'additional');
                }
            }
        }
        // If files is an associative array (keys preserved)
        elseif (is_array($files)) {
            foreach ($files as $type => $file) {
                if (is_array($file)) {
                    // Multiple files for same type
                    foreach ($file as $individualFile) {
                        $attachmentData[] = $this->storeSingleFile($individualFile, $type);
                    }
                } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                    $attachmentData[] = $this->storeSingleFile($file, $type);
                }
            }
        }

        return empty($attachmentData) ? null : $attachmentData;
    }

    /**
     * Store a single file and return its metadata.
     */
    protected function storeSingleFile($file, string $type): array
    {
        // Sanitize original filename - remove path info and potentially dangerous characters
        $originalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();

        // Generate UUID-based filename to prevent predictability and collisions
        $uuid = Str::uuid()->toString();
        $filename = $originalName . '_' . $uuid . '.' . $extension;

        $path = $file->storeAs(
            'applications/' . date('Y/m'),
            $filename,
            'public'
        );

        return [
            'name' => $originalName . '.' . $extension,
            'type' => $type,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toDateTimeString(),
        ];
    }
}