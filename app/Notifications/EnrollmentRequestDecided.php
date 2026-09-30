<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentRequestDecided extends Notification implements ShouldQueue
{
    use Queueable;

    protected EnrollmentRequest $enrollmentRequest;

    public function __construct(EnrollmentRequest $enrollmentRequest)
    {
        $this->enrollmentRequest = $enrollmentRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $status = $this->enrollmentRequest->status;
        $moduleName = $this->enrollmentRequest->module?->name ?? 'a module';
        $url = route('hive.enrollment.index');

        return (new MailMessage)
            ->subject("Your enrollment request for {$moduleName} was {$status}")
            ->line("Your {$this->enrollmentRequest->type} request for {$moduleName} has been {$status}.")
            ->action('View My Enrollments', $url);
    }

    public function toArray($notifiable)
    {
        $moduleName = $this->enrollmentRequest->module?->name ?? 'a module';

        return [
            'title' => 'Enrollment Request '.$this->enrollmentRequest->status,
            'message' => "Your request to {$this->enrollmentRequest->type} {$moduleName} was {$this->enrollmentRequest->status}.",
            'link' => route('hive.enrollment.index'),
        ];
    }
}
