<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentRequestSubmitted extends Notification implements ShouldQueue
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
        $studentName = $this->enrollmentRequest->user?->name ?? 'A student';
        $moduleName = $this->enrollmentRequest->module?->name ?? 'a module';
        $url = route('hive.enrollment.admin.index');

        return (new MailMessage)
            ->subject("Enrollment request from {$studentName}")
            ->line("{$studentName} has requested to {$this->enrollmentRequest->type} {$moduleName}.")
            ->action('Review Request', $url);
    }

    public function toArray($notifiable)
    {
        $studentName = $this->enrollmentRequest->user?->name ?? 'A student';
        $moduleName = $this->enrollmentRequest->module?->name ?? 'a module';

        return [
            'title' => 'New Enrollment Request',
            'message' => "{$studentName} requested to {$this->enrollmentRequest->type} {$moduleName}.",
            'link' => route('hive.enrollment.admin.index'),
        ];
    }
}
