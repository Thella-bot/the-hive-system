<?php namespace App\Http\Controllers;

use App\Models\Programme;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function home()
    {
        $programmes = Programme::count();
        $announcements = \App\Models\Announcement::visibleTo(auth()->user() ?? new \App\Models\User) // for guest, show all with null target
                             ->where('is_pinned', true)
                             ->latest()
                             ->take(3)
                             ->get();
        return Inertia::render('Public/Home', ['programmes_count' => $programmes, 'announcements' => $announcements]);
    }

    public function about()
    {
        return Inertia::render('Public/About');
    }

    public function programmes()
    {
        return Inertia::render('Public/Programmes', [
            'programmes' => Programme::with('modules')->get()
        ]);
    }

    public function contact()
    {
        return Inertia::render('Public/Contact');
    }
}