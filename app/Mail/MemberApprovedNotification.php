<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberApprovedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Member $member)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ihr Mitgliedsantrag wurde angenommen',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.member-approved-notification',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
