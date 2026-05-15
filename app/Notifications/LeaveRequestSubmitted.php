<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected LeaveRequest $leaveRequest;

    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $user = $this->leaveRequest->user;
        $url = route('hive.leaves.index'); // Or a direct link to the request

        return (new MailMessage)
                    ->subject("New Leave Request from {$user->name}")
                    ->line("A new leave request has been submitted by {$user->name}.")
                    ->line("Type: {$this->leaveRequest->type}")
                    ->line("Dates: {$this->leaveRequest->start_date->format('M d, Y')} to {$this->leaveRequest->end_date->format('M d, Y')}")
                    ->action('View Request', $url)
                    ->line('Please review the request at your earliest convenience.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Leave Request',
            'message' => "A new leave request has been submitted by {$this->leaveRequest->user->name}.",
            'link' => route('intranet.leaves.index'),
        ];
    }
}
