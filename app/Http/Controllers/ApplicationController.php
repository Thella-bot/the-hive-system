<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'programme_id' => 'required|exists:programmes,id',
            'variant_id' => 'nullable|exists:programme_variants,id',
        ]);

        try {
            // Link to authenticated user if available
            if (auth()->check()) {
                $validatedData['user_id'] = auth()->id();
            }

            $application = Application::create($validatedData);
            Log::info('Application created successfully', ['application_id' => $application->id]);
            return back()->with('success', 'Your application has been submitted. We\'ll be in touch shortly!');
        } catch (\Exception $e) {
            Log::error('Application submission failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'There was a problem submitting your application. Please try again later.');
        }
    }
}