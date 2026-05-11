<?php namespace App\Http\Controllers;

use App\Models\Programme;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function home()
    {
        $programmes = Programme::count();
        $announcements = \App\Models\Announcement::query()
                             ->whereNull('target_roles')
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
