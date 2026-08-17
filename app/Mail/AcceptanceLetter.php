<?php
declare(strict_types=1);

namespace App\Mail;

use App\Models\Application;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcceptanceLetter extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public User $student,
        public string $passwordResetUrl,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You Have Been Admitted to HBCI — Next Steps Inside',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.acceptance-welcome-pack',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $this->application->loadMissing(['programme', 'variant', 'user']);

        return [
            Attachment::fromData(function () {
                $registrar = \App\Models\User::role('registrar')->first();
                $student = $this->student;
                $student->loadMissing('profile');

                return Pdf::loadView('pdf.documents.acceptance', [
                    'application' => $this->application,
                    'student' => $this->student,
                    'programme' => $this->application->programme,
                    'enrollment' => $this->application,
                    'intake_date' => $this->application->admitted_at ?? now(),
                    'deadline' => now()->addDays(14),
                    'registration_fee' => 500.00,
                    'registrar_name' => $registrar ? $registrar->name : 'Registrar',
                ])->output();
            }, 'hbci-acceptance-letter-' . $this->application->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
