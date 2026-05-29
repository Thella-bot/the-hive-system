<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\ChatMessageSent;
use App\Models\ChatChannel;
use App\Models\Module;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    // Legacy: module-scoped messages
    public function index(Module $module)
    {
        $channel = ChatChannel::where('channel_type', 'module')
            ->where('channel_id', $module->id)
            ->first();

        if (!$channel) {
            return [];
        }

        return $channel->messages()->with('user')->get();
    }

    public function store(Request $request, Module $module)
    {
        $request->validate(['message' => 'required|string']);

        $channel = ChatChannel::firstOrCreate(
            ['channel_type' => 'module', 'channel_id' => $module->id],
            ['name' => $module->name]
        );

        $message = $channel->messages()->create([
            'user_id' => auth()->id(),
            'module_id' => $module->id,
            'message' => $request->message,
        ]);

        broadcast(new ChatMessageSent($message->load('user')))->toOthers();

        return $message->load('user');
    }

    // New: channel-scoped messages
    public function indexChannel(ChatChannel $channel)
    {
        return $channel->messages()->with('user')->get();
    }

    public function storeChannel(Request $request, ChatChannel $channel)
    {
        $request->validate(['message' => 'required|string']);

        $message = $channel->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        broadcast(new ChatMessageSent($message->load('user')))->toOthers();

        return $message->load('user');
    }
}
