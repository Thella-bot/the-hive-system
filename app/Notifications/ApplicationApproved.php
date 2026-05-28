<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class ApplicationApproved extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The new user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::createToken($this->user);
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $this->user->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Welcome to Honey Bee Culinary Institute!')
            ->line('Congratulations! Your application to the Honey Bee Culinary Institute has been approved.')
            ->line('An account has been created for you. To get started, you need to set your password.')
            ->action('Set Your Password', $url)
            ->line('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')])
            ->line('We are excited to have you join our community!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}