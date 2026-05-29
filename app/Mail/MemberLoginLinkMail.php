<?php

namespace App\Mail;

use App\Services\MemberMagicLoginService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberLoginLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $loginUrl)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ihr Zugangslink zum DITIB Ahlen Mitgliedskonto',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.member-login-link',
            with: [
                'expiresInMinutes' => MemberMagicLoginService::EXPIRES_IN_MINUTES,
            ],
        );
    }
}
