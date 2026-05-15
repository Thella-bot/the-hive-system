<?php

namespace App\Http\Controllers\Hive\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportUsersJob;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ImportUsersController extends Controller
{
    public function show()
    {
        return Inertia::render('Intranet/Admin/ImportUsers');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->store('imports');

        ImportUsersJob::dispatch($path, $request->user());

        Log::info('import_users.controller_queued', [
            'path' => $path,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Import job queued. Users will be notified by email.');
    }
}