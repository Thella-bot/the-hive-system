<?php

namespace App\Notifications;

use App\Models\VisitorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitorArrivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public VisitorLog $visitor)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Visitor Arrived — ' . $this->visitor->visitor_name)
            ->line($this->visitor->visitor_name . ' has arrived and is waiting for you.')
            ->when($this->visitor->purpose, fn($m) => $m->line('Purpose: ' . $this->visitor->purpose))
            ->when($this->visitor->id_number, fn($m) => $m->line('ID Number: ' . $this->visitor->id_number))
            ->action('View Visitor Log', url('/hive/visitor-logs'));
    }

    public function toArray($notifiable)
    {
        return [
            'visitor_id' => $this->visitor->id,
            'visitor_name' => $this->visitor->visitor_name,
            'purpose' => $this->visitor->purpose,
            'arrived_at' => $this->visitor->arrived_at,
        ];
    }
}
