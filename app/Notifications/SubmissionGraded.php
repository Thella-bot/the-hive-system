<?php namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubmissionGraded extends Notification
{
    use Queueable;

    public function __construct(public Submission $submission) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'assignment_title' => $this->submission->assignment->title,
            'grade' => $this->submission->grade,
            'message' => "Your submission for '{$this->submission->assignment->title}' has been graded.",
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Submission Graded: ' . $this->submission->assignment->title)
            ->line('Your grade: ' . $this->submission->grade . '%')
            ->line('Feedback: ' . ($this->submission->feedback ?: 'None'))
            ->action('View', route('intranet.assignments.show', $this->submission->assignment_id));
    }
}