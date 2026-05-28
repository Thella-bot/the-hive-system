<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\ChatMessageSent;
use App\Models\Module;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function index(Module $module)
    {
        // Eager load the user to prevent N+1 query problems
        return $module->messages()->with('user')->get();
    }

    public function store(Request $request, Module $module)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = $module->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        broadcast(new ChatMessageSent($message->load('user')))->toOthers();

        return $message->load('user');
    }
}