<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class WorkAnniversaryNotification extends Notification
{
    public function __construct(public $user, public $years) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'anniversary',
            'title' => "🎉 {$this->user->name}'s Work Anniversary!",
            'body' => "{$this->user->name} has been with HBCI for {$this->years} year" . ($this->years > 1 ? 's' : '') . ". Congratulations!",
            'link' => route('profile.show', $this->user->id),
        ];
    }
}
