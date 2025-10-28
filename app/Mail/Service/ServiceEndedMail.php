<?php

namespace App\Mail\Service;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceEndedMail extends Mailable
{
    use Queueable;
    use SerializesModels;
    public array $data;

    public function __construct(array $data)
    {
        $this->data = [
            'user' => $data['user'],
            'request' => $data['request'],
            'offer' => $data['offer'],
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Oferta Recebida',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-ended',
            with: [
                'user' => $this->data['user'],
                'request' => $this->data['request'],
                'offer' => $this->data['offer'],
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
