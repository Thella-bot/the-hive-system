<?php

namespace App\Http\Controllers\Intranet\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportUsersJob;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        ImportUsersJob::dispatch($path, auth()->user());

        return back()->with('success', 'Import job queued. Users will be notified by email.');
    }
}