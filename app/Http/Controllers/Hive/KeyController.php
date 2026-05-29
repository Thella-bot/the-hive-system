<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Key;
use App\Models\KeyAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KeyController extends Controller
{
    public function index()
    {
        $keys = Key::with(['currentAssignment.user'])->get();
        return Inertia::render('Hive/Keys/Index', ['keys' => $keys]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        Key::create($data);
        return back()->with('success', 'Key added.');
    }

    public function issue(Request $request, Key $key)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);

        KeyAssignment::create([
            'key_id' => $key->id,
            'user_id' => $data['user_id'],
            'issued_at' => now(),
            'status' => 'issued',
        ]);

        $key->update(['status' => 'issued']);
        return back()->with('success', 'Key issued.');
    }

    public function return(Key $key)
    {
        $assignment = $key->currentAssignment()->first();
        if ($assignment) {
            $assignment->update(['returned_at' => now(), 'status' => 'returned']);
        }
        $key->update(['status' => 'available']);
        return back()->with('success', 'Key returned.');
    }

    public function reportLost(Key $key)
    {
        $assignment = $key->currentAssignment()->first();
        if ($assignment) {
            $assignment->update(['returned_at' => now(), 'status' => 'lost']);
        }
        $key->update(['status' => 'lost']);
        return back()->with('success', 'Key marked as lost.');
    }
}
