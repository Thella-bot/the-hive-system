<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAssignment extends Notification implements ShouldQueue
{
    use Queueable;

    protected Assignment $assignment;

    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $url = route('intranet.assignments.show', $this->assignment);

        return (new MailMessage)
                    ->subject("New Assignment: {$this->assignment->title}")
                    ->line("A new assignment has been posted for the module: {$this->assignment->module->name}.")
                    ->line("Title: {$this->assignment->title}")
                    ->line("Due Date: {$this->assignment->due_date->format('M d, Y')}")
                    ->action('View Assignment', $url);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Assignment',
            'message' => "A new assignment, '{$this->assignment->title}', has been posted.",
            'link' => route('intranet.assignments.show', $this->assignment),
        ];
    }
}
