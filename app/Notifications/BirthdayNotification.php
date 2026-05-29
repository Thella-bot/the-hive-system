<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class BirthdayNotification extends Notification
{
    public function __construct(public $user) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'birthday',
            'title' => "🎂 Happy Birthday, {$this->user->name}!",
            'body' => "{$this->user->name} celebrates their birthday today. Send your wishes!",
            'link' => route('profile.show', $this->user->id),
        ];
    }
}
