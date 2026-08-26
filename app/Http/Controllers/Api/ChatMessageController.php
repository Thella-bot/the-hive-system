<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\ChatMessageSent;
use App\Models\ChatChannel;
use App\Models\Module;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function index(Module $module)
    {
        $channel = ChatChannel::where('channel_type', 'module')
            ->where('channel_id', $module->id)
            ->first();

        if (! $channel) {
            return [];
        }

        $this->authorize('view', $channel);

        return $channel->messages()->with('user')->orderBy('created_at', 'asc')->paginate(50);
    }

    public function store(Request $request, Module $module)
    {
        $channel = ChatChannel::getModuleChannel($module->id, $module->name);

        $this->authorize('create', $channel);

        $request->validate([
            'message' => [
                'required',
                'string',
                'max:5000',
                function ($attribute, $value, $fail) {
                    if (trim(strip_tags($value)) === '') {
                        $fail('The message cannot be empty.');
                    }
                },
            ],
            'attachments' => 'sometimes|array|max:5',
            'attachments.*.path' => 'required_with:attachments|string',
            'attachments.*.name' => 'required_with:attachments|string|max:255',
            'attachments.*.size' => 'required_with:attachments|integer|max:10240',
            'attachments.*.mime_type' => 'required_with:attachments|string',
        ]);

        $messageData = [
            'user_id' => auth()->id(),
            'module_id' => $module->id,
            'message' => $this->sanitizeMessage($request->message),
        ];

        if ($request->has('attachments')) {
            $messageData['attachments'] = $request->attachments;
        }

        $message = $channel->messages()->create($messageData);

        broadcast(new ChatMessageSent($message->load('user')))->toOthers();

        return $message->load('user');
    }

    public function indexChannel(ChatChannel $channel)
    {
        $this->authorize('view', $channel);

        return $channel->messages()->with('user')->orderBy('created_at', 'asc')->paginate(50);
    }

    public function storeChannel(Request $request, ChatChannel $channel)
    {
        $this->authorize('create', $channel);

        $request->validate([
            'message' => [
                'required',
                'string',
                'max:5000',
                function ($attribute, $value, $fail) {
                    if (trim(strip_tags($value)) === '') {
                        $fail('The message cannot be empty.');
                    }
                },
            ],
            'attachments' => 'sometimes|array|max:5',
            'attachments.*.path' => 'required_with:attachments|string',
            'attachments.*.name' => 'required_with:attachments|string|max:255',
            'attachments.*.size' => 'required_with:attachments|integer|max:10240',
            'attachments.*.mime_type' => 'required_with:attachments|string',
        ]);

        $messageData = [
            'user_id' => auth()->id(),
            'message' => $this->sanitizeMessage($request->message),
        ];

        // Set module_id for module channels to maintain legacy broadcast compatibility
        if ($channel->channel_type === 'module') {
            $messageData['module_id'] = $channel->channel_id;
        }

        if ($request->has('attachments')) {
            $messageData['attachments'] = $request->attachments;
        }

        $message = $channel->messages()->create($messageData);

        broadcast(new ChatMessageSent($message->load('user')))->toOthers();

        return $message->load('user');
    }

    public function update(Request $request, ChatChannel $channel, \App\Models\Message $message)
    {
        $this->authorize('update', $message);

        if ($message->chat_channel_id !== $channel->id) {
            abort(404);
        }

        $request->validate([
            'message' => [
                'required',
                'string',
                'max:5000',
                function ($attribute, $value, $fail) {
                    if (trim(strip_tags($value)) === '') {
                        $fail('The message cannot be empty.');
                    }
                },
            ],
        ]);

        $message->update([
            'message' => $this->sanitizeMessage($request->message),
        ]);

        return $message->load('user');
    }

    public function destroy(ChatChannel $channel, \App\Models\Message $message)
    {
        $this->authorize('delete', $message);

        if ($message->chat_channel_id !== $channel->id) {
            abort(404);
        }

        $message->delete();

        return response()->noContent();
    }

    public function storeDirectChannel(Request $request)
    {
        $request->validate([
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $participants = array_unique(array_merge([$request->user()->id], $request->participants));
        sort($participants);
        $participantStrings = array_map('strval', $participants);

        // Check if a direct channel already exists with these exact participants
        $existingChannel = ChatChannel::where('channel_type', 'direct')
            ->get()
            ->first(function ($channel) use ($participantStrings) {
                $channelParticipants = array_map('strval', $channel->participants ?? []);
                sort($channelParticipants);
                return $channelParticipants === $participantStrings;
            });

        if ($existingChannel) {
            return response()->json($existingChannel, 200);
        }

        $channel = ChatChannel::create([
            'channel_type' => 'direct',
            'channel_id' => null,
            'name' => 'Direct Message',
            'participants' => $participantStrings,
        ]);

        return response()->json($channel, 201);
    }

    public function search(ChatChannel $channel, Request $request)
    {
        $this->authorize('view', $channel);

        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $query = $request->input('q');

        return $channel->messages()
            ->with('user')
            ->where('message', 'like', '%' . $query . '%')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Sanitize message input by trimming whitespace and stripping HTML tags.
     */
    protected function sanitizeMessage(string $message): string
    {
        return trim(strip_tags($message));
    }
}
