<?php namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AssignmentCreated extends Notification
{
    use Queueable;

    public function __construct(public Assignment $assignment) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];   // both in-app and email
    }

    public function toDatabase($notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'module' => $this->assignment->module->name,
            'due_date' => $this->assignment->due_date->toDateString(),
            'message' => "New assignment: '{$this->assignment->title}' in {$this->assignment->module->name}.",
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Assignment: ' . $this->assignment->title)
            ->line('A new assignment has been posted in ' . $this->assignment->module->name)
            ->line('Due date: ' . $this->assignment->due_date->format('d M Y'))
            ->action('View Assignment', route('intranet.assignments.show', $this->assignment->id));
    }
}