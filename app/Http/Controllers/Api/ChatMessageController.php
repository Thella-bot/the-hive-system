<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\ChatMessageSent;
use App\Models\ChatChannel;
use App\Models\Module;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function __construct(): void {
        $this->authorizeResource(ChatChannel::class, 'channel');
    }

    public function index(Module $module)
    {
        $channel = ChatChannel::where('channel_type', 'module')
            ->where('channel_id', $module->id)
            ->first();

        if (! $channel) {
            return [];
        }

        $this->authorize('view', $channel);

        return $channel->messages()->with('user')->get();
    }

    public function store(Request $request, Module $module)
    {
        $channel = ChatChannel::firstOrCreate(
            ['channel_type' => 'module', 'channel_id' => $module->id],
            ['name' => $module->name]
        );

        $this->authorize('create', $channel);

        $request->validate(['message' => 'required|string|max:5000']);

        $message = $channel->messages()->create([
            'user_id' => auth()->id(),
            'module_id' => $module->id,
            'message' => $request->message,
        ]);

        broadcast(new ChatMessageSent($message->load('user')))->toOthers();

        return $message->load('user');
    }

    public function indexChannel(ChatChannel $channel)
    {
        $this->authorize('view', $channel);

        return $channel->messages()->with('user')->get();
    }

    public function storeChannel(Request $request, ChatChannel $channel)
    {
        $this->authorize('create', $channel);

        $request->validate(['message' => 'required|string|max:5000']);

        $message = $channel->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        broadcast(new ChatMessageSent($message->load('user')))->toOthers();

        return $message->load('user');
    }
}
