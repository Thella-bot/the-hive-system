<?php namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        ContactMessage::create($data);

        // Send email notification to admin (queue later)
        // Mail::to('admin@hbculinaryinstitute.co.ls')->queue(new \App\Mail\ContactFormMail($data));

        return redirect()->route('contact')->with('success', 'Thank you for your message.');
    }
}