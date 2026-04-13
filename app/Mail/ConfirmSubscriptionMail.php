<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmSubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmez votre abonnement a la newsletter',
        );
    }

    public function content(): Content
    {
        $baseUrl = rtrim(config('app.url'), '/');

        return new Content(
            view: 'emails.newsletter.confirm',
            with: [
                'subscriber'     => $this->subscriber,
                'confirmUrl'     => $baseUrl . '/api/v1/newsletter/confirm/' . $this->subscriber->token,
                'unsubscribeUrl' => $baseUrl . '/api/v1/newsletter/unsubscribe/' . $this->subscriber->token,
            ],
        );
    }
}
