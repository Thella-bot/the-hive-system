<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionReceived extends Notification implements ShouldQueue
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
        $student = $this->submission->student;
        $gradable = $this->submission->gradable;
        $url = route('hive.gradables.show', $gradable);

        return (new MailMessage)
                    ->subject("New Submission for {$gradable->title}")
                    ->line("{$student->name} has submitted their work for the assessment: {$gradable->title}.")
                    ->action('View Submission', $url);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Submission',
            'message' => "{$this->submission->student->name} has submitted their work for '{$this->submission->gradable->title}'.",
            'link' => route('hive.gradables.show', $this->submission->gradable),
        ];
    }
}
