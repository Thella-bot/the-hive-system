<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestUpdated extends Notification implements ShouldQueue
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
        $status = $this->leaveRequest->status;
        $url = route('intranet.leaves.index');

        return (new MailMessage)
                    ->subject("Your Leave Request has been {$status}")
                    ->line("Your leave request from {$this->leaveRequest->start_date->format('M d, Y')} to {$this->leaveRequest->end_date->format('M d, Y')} has been {$status}.")
                    ->action('View My Leave Requests', $url);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Leave Request Updated',
            'message' => "Your leave request has been {$this->leaveRequest->status}.",
            'link' => route('intranet.leaves.index'),
        ];
    }
}
