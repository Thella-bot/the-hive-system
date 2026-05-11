<?php namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user()->load('profile');
        return Inertia::render('Intranet/Profile/Edit', ['profile' => $user->profile]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile ?? $user->profile()->create();

        $data = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        $profile->update($data);
        return back()->with('success', 'Profile updated.');
    }
}
