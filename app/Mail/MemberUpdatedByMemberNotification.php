<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberUpdatedByMemberNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{label: string, old: ?string, new: ?string, sensitive: bool}>  $changes
     */
    public function __construct(
        public Member $member,
        public array $changes,
        public string $adminUrl,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mitglied hat eigene Daten aktualisiert: '.$this->member->member_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.member-updated-by-member-notification',
        );
    }
}
