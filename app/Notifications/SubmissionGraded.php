<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionGraded extends Notification implements ShouldQueue
{
    use Queueable;

    protected Submission $submission;

    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $assignment = $this->submission->assignment;
        $url = route('hive.assignments.show', $assignment);

        return (new MailMessage)
                    ->subject("Your submission for {$assignment->title} has been graded")
                    ->line("Your submission for the assignment '{$assignment->title}' has been graded.")
                    ->line("Grade: {$this->submission->grade}")
                    ->line("Feedback: {$this->submission->feedback}")
                    ->action('View Submission', $url);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Submission Graded',
            'message' => "Your submission for '{$this->submission->assignment->title}' has been graded.",
            'link' => route('hive.assignments.show', $this->submission->assignment),
        ];
    }
}
