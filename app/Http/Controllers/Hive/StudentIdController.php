<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentIdController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return Inertia::render('Hive/StudentIdCard', [
            'student_id' => [
                'name' => $user->name,
                'email' => $user->email,
                'student_number' => $profile?->student_number,
                'programme' => $profile?->cohort?->programme?->name,
                'cohort' => $profile?->cohort?->name,
                'photo' => $user->profile_photo_url,
                'qr_data' => $profile?->student_number ?? 'HBCI-' . $user->id,
            ],
        ]);
    }
}
