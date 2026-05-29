<?php

namespace App\Events;

use App\Models\Announcement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyAlert implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Announcement $announcement) {}

    public function broadcastOn(): array
    {
        return [new Channel('emergency')];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'body' => $this->announcement->body,
            'priority' => $this->announcement->priority,
            'created_at' => $this->announcement->created_at->toIso8601String(),
        ];
    }
}
