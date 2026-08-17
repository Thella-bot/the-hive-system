<?php
declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\MailMessage;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): MailMessage
    {
        return (new MailMessage)
            ->subject('New Contact Form Submission')
            ->greeting('Hello,')
            ->line('You have received a new contact form submission.')
            ->line('Name: ' . $this->data['name'])
            ->line('Email: ' . $this->data['email'])
            ->line('Message:')
            ->line($this->data['message'])
            ->salutation('Regards, The Hive System');
    }
}
