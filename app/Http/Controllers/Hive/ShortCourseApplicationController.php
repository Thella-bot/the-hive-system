<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\ShortCourse;
use App\Models\ShortCourseApplication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShortCourseApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ShortCourseApplication::class, 'short_course_application');
    }

    public function index(Request $request): Response
    {
        $filter = $request->query('filter', 'pending');
        $shortCourseId = $request->query('short_course_id');

        $query = ShortCourseApplication::with(['shortCourse', 'user', 'reviewer']);

        if ($shortCourseId) {
            $query->where('short_course_id', $shortCourseId);
        }

        if ($filter !== 'all') {
            $query->where('short_course_applications.status', $filter);
        }

        $shortCourseApplications = $query->latest()->paginate(15);

        return Inertia::render('Hive/ShortCourseApplications/Index', [
            'shortCourseApplications' => $shortCourseApplications,
            'filter' => $filter,
            'shortCourseId' => $shortCourseId,
        ]);
    }

    public function review(Request $request, ShortCourseApplication $shortCourseApplication): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('review', $shortCourseApplication);

        $data = $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        $shortCourseApplication->update([
            'status' => $data['status'],
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Application status updated.');
    }
}
