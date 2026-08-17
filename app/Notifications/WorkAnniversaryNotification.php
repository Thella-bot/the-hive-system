<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WorkAnniversaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            'body' => "{$this->user->name} has been with " . config('institution.abbreviation') . " for {$this->years} year" . ($this->years > 1 ? 's' : '') . ". Congratulations!",
            'link' => route('profile.show', $this->user->id),
        ];
    }
}
