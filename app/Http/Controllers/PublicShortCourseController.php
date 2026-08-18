<?php

namespace App\Http\Controllers;

use App\Models\ShortCourse;
use App\Models\ShortCourseApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PublicShortCourseController extends Controller
{
    public function index()
    {
        $shortCourses = ShortCourse::with('courseable')
            ->active()
            ->open()
            ->latest()
            ->get()
            ->map(fn($sc) => array_merge($sc->toArray(), [
                'start_date' => $sc->start_date?->format('M j, Y'),
                'end_date'   => $sc->end_date?->format('M j, Y'),
            ]));

        return Inertia::render('Public/ShortCourses/Index', [
            'shortCourses' => $shortCourses,
        ]);
    }

    public function apply(ShortCourse $shortCourse)
    {
        if (!$shortCourse->is_active || !$shortCourse->accepting_applications) {
            return redirect()->route('programmes')
                ->with('error', 'Applications for this short course are currently closed.');
        }

        $sc = $shortCourse->load('courseable');
        $data = $sc->toArray();
        $data['start_date'] = $sc->start_date?->format('M j, Y');
        $data['end_date']   = $sc->end_date?->format('M j, Y');

        return Inertia::render('Public/ApplyShortCourse', [
            'shortCourse' => $data,
        ]);
    }

    public function store(Request $request, ShortCourse $shortCourse): RedirectResponse
    {
        if (!$shortCourse->is_active || !$shortCourse->accepting_applications) {
            return redirect()->route('programmes')
                ->with('error', 'Applications for this short course are currently closed.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['short_course_id'] = $shortCourse->id;
        $data['applied_at'] = now();

        if (auth()->check()) {
            $data['user_id'] = auth()->id();
        }

        try {
            ShortCourseApplication::create($data);
            Log::info('Short course application submitted', ['short_course_id' => $shortCourse->id]);

            return back()->with('success', 'Your application has been submitted! We\'ll be in touch shortly.');
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'UNIQUE')) {
                return back()->with('error', 'You have already applied for this short course.')
                    ->withInput();
            }
            Log::error('Short course application failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'There was a problem submitting your application. Please try again.')
                ->withInput();
        }
    }
}
