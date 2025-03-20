<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecoverPasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitação para redefinição de senha',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recover-password',
            with: [
                'user' => $this->user,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
