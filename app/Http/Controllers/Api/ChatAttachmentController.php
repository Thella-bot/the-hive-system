<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatAttachmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip',
        ]);

        $file = $request->file('file');
        $path = $file->store('chat-attachments', 'private');

        return response()->json([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'url' => route('attachments.download', ['path' => $path]),
        ], 201);
    }

    public function show(string $path)
    {
        $fullPath = 'chat-attachments/' . basename($path);

        if (!Storage::disk('private')->exists($fullPath)) {
            abort(404);
        }

        return Storage::disk('private')->download($fullPath);
    }
}
