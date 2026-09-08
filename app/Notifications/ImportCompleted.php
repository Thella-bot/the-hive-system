<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected int $successCount;

    protected int $failureCount;

    protected int $duplicateCount;

    protected int $skippedCount;

    protected array $errors;

    protected ?string $jobError;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $successCount, int $failureCount, int $duplicateCount, int $skippedCount, array $errors, ?string $jobError = null)
    {
        $this->successCount = $successCount;
        $this->failureCount = $failureCount;
        $this->duplicateCount = $duplicateCount;
        $this->skippedCount = $skippedCount;
        $this->errors = $errors;
        $this->jobError = $jobError;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getDuplicateCount(): int
    {
        return $this->duplicateCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getJobError(): ?string
    {
        return $this->jobError;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('User Import Completed')
            ->line('The user import job has finished.');

        if ($this->jobError) {
            $mail->error()
                ->line('The job failed with a critical error:')
                ->line($this->jobError);

            return $mail;
        }

        $mail->line("Successfully imported: {$this->successCount} users.")
            ->line("Failed to import: {$this->failureCount} users.")
            ->line("Duplicate rows skipped: {$this->duplicateCount} users.")
            ->line("Users not found (skipped): {$this->skippedCount} users.");

        if ($this->failureCount > 0 || $this->duplicateCount > 0 || $this->skippedCount > 0) {
            $mail->line('Here are the details:');
            foreach ($this->errors as $error) {
                $mail->line("- {$error}");
            }
        }

        $mail->action('View Users', url('/users'));

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if ($this->jobError) {
            return [
                'title' => 'User Import Failed',
                'message' => 'The user import job failed with a critical error.',
                'level' => 'error',
            ];
        }

        return [
            'title' => 'User Import Completed',
            'message' => "Imported {$this->successCount} users, {$this->failureCount} failed, {$this->duplicateCount} duplicates skipped, {$this->skippedCount} not found.",
            'level' => ($this->failureCount > 0 || $this->duplicateCount > 0 || $this->skippedCount > 0) ? 'warning' : 'success',
        ];
    }
}
