<?php

namespace App\Http\Controllers\Hive;

use App\Actions\Hive\CreateNewStudent;
use App\Http\Controllers\Controller;
use App\Models\ProgrammeSought;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProgrammeSoughtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $applications = ProgrammeSought::with('programme')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('Hive/Applications/Index', [
            'applications' => $applications,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProgrammeSought $application): Response
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $application->load('programme');
        return Inertia::render('Hive/Applications/Show', [
            'application' => $application,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProgrammeSought $application, CreateNewStudent $creator): RedirectResponse
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $tempPassword = Str::random(12);
        $creator->create([
            'name' => $application->name,
            'email' => $application->email,
            'password' => $tempPassword,
            'password_confirmation' => $tempPassword,
            'programme_id' => $application->programme_id,
        ]);

        $application->update(['status' => 'approved']);

        return redirect()->route('hive.applications.index')->with('success', 'Application approved and student created.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgrammeSought $application): RedirectResponse
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'school-admin'])) {
            abort(403);
        }
        $application->update(['status' => 'rejected']);

        return redirect()->route('hive.applications.index')->with('success', 'Application rejected.');
    }
}