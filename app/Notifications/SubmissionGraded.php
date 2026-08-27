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
        $gradable = $this->submission->gradable;
        $url = route('hive.gradables.show', $gradable);

        return (new MailMessage)
                    ->subject("Your submission for {$gradable->title} has been graded")
                    ->line("Your submission for the assessment '{$gradable->title}' has been graded.")
                    ->line("Grade: {$this->submission->grade}")
                    ->line("Feedback: {$this->submission->feedback}")
                    ->action('View Submission', $url);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Submission Graded',
            'message' => "Your submission for '{$this->submission->gradable->title}' has been graded.",
            'link' => route('hive.gradables.show', $this->submission->gradable),
        ];
    }
}
