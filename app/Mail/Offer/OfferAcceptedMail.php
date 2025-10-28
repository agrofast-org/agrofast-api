<?php

namespace App\Mail\Offer;

use App\Models\Hr\User;
use App\Models\Transport\Offer;
use App\Models\Transport\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Summary of OfferAcceptedMail.
 *
 * Essa classe é responsável por enviar um email de aviso ao **transportador**.
 */
class OfferAcceptedMail extends Mailable
{
    use Queueable;
    use SerializesModels;
    public array $data;

    public function __construct(array $data)
    {
        $this->data = [
            'user' => User::find($data['user_id']),
            'request' => Request::find($data['request_id']),
            'offer' => Offer::find($data['offer_id']),
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Oferta Aceita',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.offer-accepted',
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
