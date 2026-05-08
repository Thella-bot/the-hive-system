<?php namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leave) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'leave_id' => $this->leave->id,
            'status' => $this->leave->status,
            'start_date' => $this->leave->start_date->toDateString(),
            'end_date' => $this->leave->end_date->toDateString(),
            'message' => "Your leave request from {$this->leave->start_date->format('d M')} to {$this->leave->end_date->format('d M')} has been {$this->leave->status}.",
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Leave Request ' . ucfirst($this->leave->status))
            ->line('Your leave request has been ' . $this->leave->status . '.')
            ->line('Period: ' . $this->leave->start_date->format('d M') . ' - ' . $this->leave->end_date->format('d M'))
            ->action('View', route('intranet.leaves.index'));
    }
}