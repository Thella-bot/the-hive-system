<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Programme;
use App\Models\ShortCourse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function home()
    {
        $programmes = Programme::with(['variants' => fn($q) => $q->where('is_active', true)->limit(2)])
            ->whereHas('variants', fn($q) => $q->where('is_active', true))
            ->take(3)
            ->get();

        $shortCourses = ShortCourse::active()->open()
            ->whereNull('start_date')
            ->orWhere('start_date', '>=', now())
            ->take(6)
            ->get();

        $announcements = \App\Models\Announcement::query()
            ->whereNull('target_roles')
            ->where('is_pinned', true)
            ->latest()
            ->take(3)
            ->get();

        return Inertia::render('Public/Home', [
            'programmes' => $programmes,
            'shortCourses' => $shortCourses,
            'announcements' => $announcements,
        ]);
    }

    public function about()
    {
        return Inertia::render('Public/About');
    }

    public function programmes()
    {
        $programmes = Programme::with([
            'variants' => fn($q) => $q->where('is_active', true),
            'shortCourses' => fn($q) => $q->active()->open(),
            'modules',
        ])->get();

        // Also collect department-level short courses
        $departmentCourses = ShortCourse::with('courseable')
            ->active()->open()
            ->get()
            ->filter(fn($sc) => $sc->courseable_type === 'App\\Models\\Department');

        return Inertia::render('Public/Programmes', [
            'programmes' => $programmes,
            'departmentCourses' => $departmentCourses,
        ]);
    }

    public function contact()
    {
        return Inertia::render('Public/Contact');
    }

    public function apply()
    {
        $programmes = Programme::with(['variants' => fn($q) => $q->where('is_active', true)])
            ->whereHas('variants', fn($q) => $q->where('is_active', true))
            ->get();

        return Inertia::render('Public/Apply', [
            'programmes' => $programmes,
        ]);
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($data);

        return back()->with('success', 'Your message has been sent. We\'ll get back to you shortly.');
    }
}